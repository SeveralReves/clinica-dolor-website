<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialist;

class WebController extends Controller
{
    public function home(Request $request)
    {   
        $specialists = Specialist::where('is_active', 1)->get();
        return view('welcome', compact('specialists'));
    }
    public function thanks(Request $request, $reference)
    {   
        $appointment = \App\Models\Appointment::with(['patient', 'specialist'])
            ->where('reference_id', $reference)
            ->firstOrFail();

        return view('schedule-thanks', compact('appointment'));
    }
    public function schedule(Request $request)
    {   
        $specialists = Specialist::with('schedules')->get()->map(function($s) {
            $formattedHours = [];
            
            foreach ($s->schedules as $slot) {
                $start = \Carbon\Carbon::parse($slot->start_time);
                $end = \Carbon\Carbon::parse($slot->end_time);

                // Creamos intervalos de 1 hora
                // Si quieres turnos de 30 min, cambia '1 hour' por '30 minutes'
                $period = \Carbon\CarbonPeriod::create($start, '1 hour', $end);

                foreach ($period as $time) {
                    // Evitamos agregar la hora exacta de salida (si sale a las 12, no hay cita a las 12)
                    if ($time->format('H:i:s') !== $end->format('H:i:s')) {
                        $formattedHours[$slot->day][] = $time->format('h:i A');
                    }
                }
            }

            return [
                'id' => $s->id,
                'name' => $s->name,
                'role' => $s->specialty,
                'photo_url' => $s->photo_url,
                'hours' => $formattedHours,
            ];
        });

        return view('schedule', compact('specialists'));
    }
}
