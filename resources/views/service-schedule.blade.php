@extends('layouts.default')

@section('content')

    @include('components.service-schedule', [
        'title'    => 'Reserva tu <span>Servicio</span>',
        'subtitle' => 'Elige el servicio que necesitas y selecciona el horario que mejor se adapte a ti.',
        'services' => $services,
    ])

@stop
