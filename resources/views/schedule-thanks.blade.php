@extends('layouts.default')

@section('content')

    @include('components.schedule-thanks', [
        'id' => $id,
        'title' => '¡Gracias por agendar tu cita!',
        'subtitle' => 'Hemos enviado un correo electrónico de confirmación a <strong>user@email.com</strong>. Por favor, llegue 15 minutos antes.',
        'specialists' => [
            'id' => 1,
            'name' => 'Dra. CARMEN CARRILLO',
            'role' => 'Medicina del Dolor - Anestesiólogo',
            'description' => 'Con más de una década de experiencia, la Dra. Carrillo es la especialista experta en Medicina del Dolor.',
            'photo' => [
                'url' => asset('/images/doctors/doctor-1.webp'),
                'alt' => 'Dra. CARMEN CARRILLO'
            ],
            'hours' => [
                'monday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '02:00 PM', '03:00 PM'],
                'wednesday' => ['09:00 AM', '5:00 PM'],
                'friday' => ['09:00 AM', '5:00 PM']
            ]
        ],
        'status' => 'scheduled',
        'phone' => "04242965626",
        'email' => "user@email.com",
        'reason' => "asdasdas",
        'date' => "2026-01-30T04:00:00.000Z",
        'hour' => "09:00 AM",
    ])

@stop