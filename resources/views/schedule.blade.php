@extends('layouts.default')

@section('content')

    @include('components.schedule', [
        'wp_action' => '/api/appointments',
        'title' => 'Agenda tu <span>Alivio</span>',
        'subtitle' => 'Encuentre al especialista adecuado y asegure un horario que funcione para su proceso de manejo del dolor.',
        'specialists' => $specialists
    ])

@stop