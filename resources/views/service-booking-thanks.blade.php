@extends('layouts.default')

@section('content')

    @include('components.service-booking-thanks', [
        'id'       => $booking->reference_id,
        'title'    => '¡Reserva Registrada!',
        'subtitle' => 'Tu solicitud ha sido recibida. Recibirás confirmación a <strong>' . $booking->patient->email . '</strong>.',

        'service' => [
            'title'       => $booking->service->title,
            'description' => $booking->service->description,
            'photo_url'   => $booking->service->photo_url,
        ],

        'status' => $booking->status,
        'date'   => $booking->date->translatedFormat('l, d \d\e F \d\e Y'),
        'hour'   => $booking->hour,
        'phone'  => $booking->patient->phone,
        'email'  => $booking->patient->email,
        'notes'  => $booking->notes,
    ])

@stop
