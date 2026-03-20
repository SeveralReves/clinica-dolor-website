<?php 
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email',
            'phone'         => 'required|string',
            'birthday'      => 'required',
            'specialist_id' => 'required|exists:specialists,id',
            'date'          => 'required',
            'hour'          => 'required|string',
            'reason'        => 'nullable|string',
        ]);

        // 1. Normalizar fecha y hora para la comparación
        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $hour = $validated['hour'];

        // 2. VALIDACIÓN DE DISPONIBILIDAD
        // Buscamos si ya existe una cita para ese doctor, ese día y esa hora
        $isOccupied = Appointment::where('specialist_id', $validated['specialist_id'])
            ->where('date', $date)
            ->where('hour', $hour)
            ->where('status', '!=', 'cancelled') // Ignoramos las citas que fueron canceladas
            ->exists();

        if ($isOccupied) {
            return response()->json([
                'message' => 'El especialista ya tiene una cita programada para este horario.',
                'errors'  => [
                    'hour' => ['Este horario ya no está disponible.']
                ]
            ], 422);
        }

        // 3. PROCESO DE GUARDADO (Transacción)
        return DB::transaction(function () use ($validated, $date, $hour) {
            
            // Buscar o Crear Paciente
            $patient = Patient::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name'     => $validated['name'],
                    'phone'    => $validated['phone'],
                    'birthday' => Carbon::parse($validated['birthday'])->format('Y-m-d'),
                ]
            );

            // Crear la Cita
            $appointment = Appointment::create([
                'patient_id'    => $patient->id,
                'specialist_id' => $validated['specialist_id'],
                'date'          => $date,
                'hour'          => $hour,
                'reason'        => $validated['reason'],
                'status'        => 'pending'
            ]);

            return response()->json([
                'message'     => 'Cita registrada con éxito',
                'appointment' => $appointment->load(['patient', 'specialist'])
            ], 201);
        });
    }
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'specialist']);

        // Filtro por búsqueda (Referencia, Nombre paciente, Email paciente)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($search) use ($q) {
                $search->where('reference_id', 'like', "%$q%")
                      ->orWhereHas('patient', function($p) use ($q) {
                          $p->where('name', 'like', "%$q%")
                            ->orWhere('email', 'like', "%$q%");
                      });
            });
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderBy('date', 'desc')
                     ->orderBy('hour', 'desc')
                     ->paginate($request->per_page ?? 10);
    }
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
            'date'   => 'sometimes|date',
            'hour'   => 'sometimes|string',
        ]);

        // Si el estado cambia, registramos el log automáticamente
        if ($request->has('status') && $request->status !== $appointment->status) {
            $labels = [
                'confirmed' => 'Cita Confirmada',
                'cancelled' => 'Cita Cancelada',
                'completed' => 'Consulta Finalizada',
                'pending'   => 'Cita en Espera'
            ];

            $appointment->logs()->create([
                'status_label' => $labels[$request->status] ?? 'Estado Actualizado',
                'description'  => 'El estado de la cita fue cambiado por el personal administrativo.'
            ]);
        }

        $appointment->update($validated);

        return response()->json(['message' => 'Cita actualizada', 'appointment' => $appointment->load('patient')]);
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return response()->json(['message' => 'Cita eliminada']);
    }
    public function getBusySlots(Request $request)
    {
        $request->validate([
            'specialist_id' => 'required|exists:specialists,id',
            'date' => 'required|date',
        ]);

        $busySlots = Appointment::where('specialist_id', $request->specialist_id)
            ->whereDate($request->date)
            ->where('status', '!=', 'cancelled')
            ->pluck('hour') // Extrae solo la columna 'hour'
            ->toArray();

        return response()->json($busySlots);
    }
    public function checkStatus(Request $request)
    {
        $request->validate([
            'search' => 'required|string'
        ]);

        $query = $request->search;

        $appointment = Appointment::with(['patient', 'specialist', 'logs'])
            ->where('reference_id', $query)
            // O buscamos a través de la relación con el paciente
            ->orWhereHas('patient', function($q) use ($query) {
                $q->where('email', $query);
            })
            ->orderBy('date', 'desc')
            ->orderBy('hour', 'desc')
            ->first();


        if (!$appointment) {
            return response()->json(['message' => 'No encontramos ninguna cita con esos datos.'], 404);
        }

        return response()->json($appointment);
    }
}