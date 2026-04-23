<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Patient;
use App\Models\Specialist;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total'       => Appointment::count(),
            'pending'     => Appointment::where('status', 'pending')->count(),
            'confirmed'   => Appointment::where('status', 'confirmed')->count(),
            'completed'   => Appointment::where('status', 'completed')->count(),
            'cancelled'   => Appointment::where('status', 'cancelled')->count(),
            'patients'    => Patient::count(),
            'specialists' => Specialist::where('is_active', true)->count(),
        ];

        $appointments = Appointment::with(['patient:id,name,email,phone', 'specialist:id,name,specialty'])
            ->where('date', '>=', now()->subMonth()->startOfMonth())
            ->where('date', '<=', now()->addMonths(2)->endOfMonth())
            ->orderBy('date')
            ->orderBy('hour')
            ->get()
            ->map(fn ($a) => [
                'id'           => $a->id,
                'reference_id' => $a->reference_id,
                'date'         => $a->date->format('Y-m-d'),
                'hour'         => $a->hour,
                'status'       => $a->status,
                'reason'       => $a->reason,
                'patient'      => $a->patient
                    ? ['name' => $a->patient->name, 'email' => $a->patient->email, 'phone' => $a->patient->phone]
                    : null,
                'specialist'   => $a->specialist
                    ? ['name' => $a->specialist->name, 'specialty' => $a->specialist->specialty]
                    : null,
            ])
            ->values();

        return view('dashboard', compact('stats', 'appointments'));
    }
    public function users(Request $request)
    {   
        $users = User::all();

        return view('dashboard.users', compact('users'));
    }
    public function specialists(Request $request)
    {   
        // $specialists = Specialists::all();

        return view('dashboard.specialists');
    }
    public function appointments(Request $request)
    {
        return view('dashboard.appointments');
    }

    public function gallery(Request $request)
    {
        return view('dashboard.gallery');
    }

    public function services(Request $request)
    {
        return view('dashboard.services');
    }

    public function serviceBookings(Request $request)
    {
        return view('dashboard.service-bookings');
    }
}
