<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Specialist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'date'   => 'sometimes|date',
            'hour'   => 'sometimes|string',
            'reason' => 'sometimes|nullable|string',
        ]);

        // Log automático cuando cambia el estado
        if ($request->has('status') && $request->status !== $appointment->status) {
            $labels = [
                'confirmed' => 'Cita Confirmada',
                'cancelled'  => 'Cita Cancelada',
                'completed'  => 'Consulta Finalizada',
                'pending'    => 'Cita en Espera',
            ];

            $appointment->logs()->create([
                'status_label' => $labels[$request->status] ?? 'Estado Actualizado',
                'description'  => 'El estado de la cita fue cambiado por el personal administrativo.',
            ]);
        }

        // Log automático cuando se pospone (cambia fecha u hora)
        if (($request->has('date') || $request->has('hour')) && !$request->has('status')) {
            $appointment->logs()->create([
                'status_label' => 'Cita Reprogramada',
                'description'  => 'La cita fue reprogramada por el personal administrativo.',
            ]);
        }

        $appointment->update($validated);

        return response()->json([
            'message'     => 'Cita actualizada',
            'appointment' => $appointment->load(['patient', 'specialist', 'logs']),
        ]);
    }

    /**
     * Registra el envío de un recordatorio y devuelve los datos de contacto del paciente.
     */
    public function sendReminder(Appointment $appointment)
    {
        $appointment->load(['patient', 'specialist']);

        $appointment->logs()->create([
            'status_label' => 'Recordatorio Enviado',
            'description'  => 'Se envió un recordatorio de cita al paciente.',
        ]);

        return response()->json([
            'message'     => 'Recordatorio registrado',
            'appointment' => $appointment,
        ]);
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
            ->whereDate('date', $request->date)
            ->where('status', '!=', 'cancelled')
            ->pluck('hour')
            ->toArray();

        return response()->json($busySlots);
    }

    /**
     * Devuelve las horas disponibles para un especialista en una fecha concreta.
     * Prioridad: disponibilidad custom > horario regular semanal.
     * Endpoint público (usado por el formulario de reserva del front).
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'specialist_id' => 'required|exists:specialists,id',
            'date'          => 'required|date',
        ]);

        $specialist = Specialist::with(['regularSchedules', 'customAvailabilities'])
            ->findOrFail($request->specialist_id);

        $date = Carbon::parse($request->date)->format('Y-m-d');

        // 1. Obtener disponibilidad (custom tiene prioridad)
        $availability = $specialist->getAvailabilityForDate($date);

        if ($availability->isEmpty()) {
            return response()->json(['available_hours' => []]);
        }

        // 2. Generar slots horarios (intervalos de 1 hora)
        $allHours = [];
        foreach ($availability as $slot) {
            $start  = Carbon::parse($slot->start_time);
            $end    = Carbon::parse($slot->end_time);
            $period = CarbonPeriod::create($start, '1 hour', $end);

            foreach ($period as $time) {
                if ($time->format('H:i:s') !== $end->format('H:i:s')) {
                    $allHours[] = $time->format('h:i A');
                }
            }
        }

        // 3. Restar los slots ya ocupados por citas vigentes
        $busySlots = Appointment::where('specialist_id', $specialist->id)
            ->whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('hour')
            ->toArray();

        $availableHours = array_values(array_diff($allHours, $busySlots));

        return response()->json(['available_hours' => $availableHours]);
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