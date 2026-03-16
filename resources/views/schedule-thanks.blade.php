@extends('layouts.default')

@section('content')

    @include('components.schedule-thanks', [
        'id' => $appointment->reference_id,
        'title' => '¡Gracias por agendar tu cita!',
        'subtitle' => 'Hemos enviado un correo electrónico de confirmación a <strong>' . $appointment->patient->email . '</strong>. Por favor, llegue 15 minutos antes.',
        
        // Datos del Especialista desde la relación
        'specialist' => [
            'id' => $appointment->specialist->id,
            'name' => $appointment->specialist->name,
            'role' => $appointment->specialist->specialty,
            'description' => $appointment->specialist->description,
            'photo' => [
                'url' => asset('storage/' . $appointment->specialist->photo_path),
                'alt' => $appointment->specialist->name
            ],
        ],
        
        // Datos de la Cita y Paciente
        'status' => $appointment->status,
        'phone'  => $appointment->patient->phone,
        'email'  => $appointment->patient->email,
        'reason' => $appointment->reason,
        
        // Formateamos la fecha: "Miércoles, 25 de Marzo de 2026"
        'date'   => $appointment->date->translatedFormat('l, d \d\e F \d\e Y'),
        'hour'   => $appointment->hour,
    ])

@stop