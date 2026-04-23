<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialist;
use App\Models\GalleryItem;
use App\Models\Service;

class WebController extends Controller
{
    public function consult(Request $request)
    {   
        return view('consult');
    }
    public function home(Request $request)
    {
        $specialists = Specialist::where('is_active', 1)->get();

        $galleryItems = GalleryItem::where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        $serviceRooms = Service::with('schedules')
            ->where('is_active', true)
            ->latest()
            ->get();

        return view('welcome', compact('specialists', 'galleryItems', 'serviceRooms'));
    }
    public function thanks(Request $request, $reference)
    {   
        $appointment = \App\Models\Appointment::with(['patient', 'specialist'])
            ->where('reference_id', $reference)
            ->firstOrFail();

        return view('schedule-thanks', compact('appointment'));
    }
    public function serviceSchedule(Request $request)
    {
        $today   = now()->toDateString();
        $horizon = now()->addMonths(3)->toDateString();

        $services = Service::with([
            'schedules',
            'customAvailabilities' => fn($q) => $q->whereBetween('date', [$today, $horizon]),
        ])->where('is_active', true)->latest()->get()->map(function ($s) {
            $scheduledDays = $s->schedules->pluck('day')->unique()->values()->toArray();

            $blockedDates = $s->customAvailabilities
                ->where('is_available', false)
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->values()->toArray();

            $customEnabledDates = $s->customAvailabilities
                ->where('is_available', true)
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->values()->toArray();

            return [
                'id'                   => $s->id,
                'title'                => $s->title,
                'description'          => $s->description,
                'photo_url'            => $s->photo_url,
                'scheduled_days'       => $scheduledDays,
                'blocked_dates'        => $blockedDates,
                'custom_enabled_dates' => $customEnabledDates,
            ];
        });

        return view('service-schedule', compact('services'));
    }

    public function serviceBookingThanks(Request $request, $reference)
    {
        $booking = \App\Models\ServiceBooking::with(['patient', 'service'])
            ->where('reference_id', $reference)
            ->firstOrFail();

        return view('service-booking-thanks', compact('booking'));
    }

    public function schedule(Request $request)
    {
        $today    = now()->toDateString();
        $horizon  = now()->addMonths(3)->toDateString();

        $specialists = Specialist::with([
            'schedules',
            'customAvailabilities' => fn($q) => $q->whereBetween('date', [$today, $horizon]),
        ])->get()->map(function ($s) {
            // Horarios semanales → objeto { monday: ['09:00 AM', ...], ... }
            $formattedHours = [];
            foreach ($s->schedules as $slot) {
                $start  = \Carbon\Carbon::parse($slot->start_time);
                $end    = \Carbon\Carbon::parse($slot->end_time);
                $period = \Carbon\CarbonPeriod::create($start, '1 hour', $end);

                foreach ($period as $time) {
                    if ($time->format('H:i:s') !== $end->format('H:i:s')) {
                        $formattedHours[$slot->day][] = $time->format('h:i A');
                    }
                }
            }

            // Fechas bloqueadas en los próximos 3 meses (is_available = false)
            $blockedDates = $s->customAvailabilities
                ->where('is_available', false)
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->values()
                ->toArray();

            // Fechas con horario especial habilitado (is_available = true)
            // Permite habilitar días fuera del horario regular (ej: un sábado puntual)
            $customEnabledDates = $s->customAvailabilities
                ->where('is_available', true)
                ->pluck('date')
                ->map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'))
                ->values()
                ->toArray();

            return [
                'id'                   => $s->id,
                'name'                 => $s->name,
                'role'                 => $s->specialty,
                'photo_url'            => $s->photo_url,
                // (object) fuerza serialización como {} incluso cuando el array está vacío
                'hours'                => (object) $formattedHours,
                'blocked_dates'        => $blockedDates,
                'custom_enabled_dates' => $customEnabledDates,
            ];
        });

        return view('schedule', compact('specialists'));
    }
}
