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
    public function index()
    {
      // Útil para el dashboard de la clínica
      $appointments = Appointment::with(['patient', 'specialist'])
      ->orderBy('date', 'asc')
      ->orderBy('hour', 'asc')
      ->get();
      
      return response()->json($appointments);
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
}