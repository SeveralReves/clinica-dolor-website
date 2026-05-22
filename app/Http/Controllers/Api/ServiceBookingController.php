<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ServiceBookingBooked;
use App\Mail\ServiceBookingConfirmed;
use App\Models\Patient;
use App\Models\Service;
use App\Models\ServiceBooking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ServiceBookingController extends Controller
{
    public function index(Request $request)
    {
        $query = ServiceBooking::with(['service', 'patient']);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($search) use ($q) {
                $search->where('reference_id', 'like', "%$q%")
                    ->orWhereHas('patient', function ($p) use ($q) {
                        $p->where('name', 'like', "%$q%")
                            ->orWhere('email', 'like', "%$q%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', $request->service_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        return $query->orderBy('date', 'desc')
            ->orderBy('hour', 'desc')
            ->paginate($request->per_page ?? 15);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email',
            'phone'      => 'required|string',
            'birthday'   => 'required',
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
            'hour'       => 'required|string',
            'notes'      => 'nullable|string',
        ]);

        $date = Carbon::parse($validated['date'])->format('Y-m-d');
        $hour = $validated['hour'];

        $service = Service::with(['schedules', 'customAvailabilities'])->findOrFail($validated['service_id']);

        $capacity = $this->getCapacityForSlot($service, $date);

        $existingCount = ServiceBooking::where('service_id', $validated['service_id'])
            ->where('date', $date)
            ->where('hour', $hour)
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($existingCount >= $capacity) {
            return response()->json([
                'message' => 'Este horario ya no tiene cupos disponibles.',
                'errors'  => ['hour' => ['Este horario ya no está disponible.']],
            ], 422);
        }

        return DB::transaction(function () use ($validated, $date, $hour) {
            $patient = Patient::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name'     => $validated['name'],
                    'phone'    => $validated['phone'],
                    'birthday' => Carbon::parse($validated['birthday'])->format('Y-m-d'),
                ]
            );

            $booking = ServiceBooking::create([
                'service_id' => $validated['service_id'],
                'patient_id' => $patient->id,
                'date'       => $date,
                'hour'       => $hour,
                'status'     => 'pending',
                'notes'      => $validated['notes'] ?? null,
            ]);

            $booking->load(['patient', 'service']);

            // Notificar al administrador
            $adminEmail = config('mail.admin_notification_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->queue(new ServiceBookingBooked($booking));
            }

            // Confirmar al paciente
            Mail::to($booking->patient->email)->queue(new ServiceBookingConfirmed($booking));

            return response()->json([
                'message' => 'Reserva registrada con éxito',
                'booking' => $booking,
            ], 201);
        });
    }

    public function update(Request $request, ServiceBooking $serviceBooking)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:pending,confirmed,cancelled,completed',
            'date'   => 'sometimes|date',
            'hour'   => 'sometimes|string',
            'notes'  => 'sometimes|nullable|string',
        ]);

        $serviceBooking->update($validated);

        return response()->json([
            'message' => 'Reserva actualizada',
            'booking' => $serviceBooking->load(['patient', 'service']),
        ]);
    }

    public function destroy(ServiceBooking $serviceBooking)
    {
        $serviceBooking->delete();
        return response()->json(['message' => 'Reserva eliminada']);
    }

    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'date'       => 'required|date',
        ]);

        $service = Service::with(['schedules', 'customAvailabilities'])->findOrFail($request->service_id);
        $date    = Carbon::parse($request->date)->format('Y-m-d');

        $availability = $service->getAvailabilityForDate($date);

        if ($availability->isEmpty()) {
            return response()->json(['available_slots' => []]);
        }

        $allSlots = [];
        foreach ($availability as $slot) {
            $capacity = $slot->capacity_override ?? $slot->capacity ?? 1;
            $start    = Carbon::parse($slot->start_time);
            $end      = Carbon::parse($slot->end_time);
            $period   = CarbonPeriod::create($start, '1 hour', $end);

            foreach ($period as $time) {
                if ($time->format('H:i:s') !== $end->format('H:i:s')) {
                    $hourStr = $time->format('h:i A');
                    if (!isset($allSlots[$hourStr])) {
                        $allSlots[$hourStr] = ['hour' => $hourStr, 'capacity' => 0];
                    }
                    $allSlots[$hourStr]['capacity'] += $capacity;
                }
            }
        }

        $bookedCounts = ServiceBooking::where('service_id', $service->id)
            ->whereDate('date', $date)
            ->where('status', '!=', 'cancelled')
            ->select('hour', DB::raw('count(*) as total'))
            ->groupBy('hour')
            ->pluck('total', 'hour')
            ->toArray();

        $availableSlots = [];
        foreach ($allSlots as $hourStr => $slot) {
            $booked    = $bookedCounts[$hourStr] ?? 0;
            $remaining = $slot['capacity'] - $booked;
            if ($remaining > 0) {
                $availableSlots[] = [
                    'hour'      => $hourStr,
                    'remaining' => $remaining,
                    'capacity'  => $slot['capacity'],
                ];
            }
        }

        return response()->json(['available_slots' => $availableSlots]);
    }

    private function getCapacityForSlot(Service $service, string $date): int
    {
        $custom = $service->customAvailabilities->firstWhere('date', $date);
        if ($custom) {
            return $custom->capacity_override ?? 1;
        }

        $dayName   = strtolower(Carbon::parse($date)->format('l'));
        $schedules = $service->schedules->where('day', $dayName);

        return $schedules->sum('capacity') ?: 1;
    }
}
