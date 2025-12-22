@extends('layouts.default')

@section('content')

    @include('components.schedule', [
        'wp_action' => '/api/booking',
        'title' => 'Agenda tu <span>Alivio</span>',
        'subtitle' => 'Encuentre al especialista adecuado y asegure un horario que funcione para su proceso de manejo del dolor.',
        'specialists' => [
            [
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
            [
                'id' => 2,
                'name' => 'Dr. MARIA GUILLEN',
                'role' => 'Anestesiólogo',
                'description' => 'Como médico en jefe de Clínica del Dolor Apure, la Dra. Guillen se especializa en Anestesiología.',
                'photo' => [
                    'url' => asset('/images/doctors/doctor-2.webp'),
                    'alt' => 'Dra. CARMEN CARRILLO'
                ],
                'hours' => [
                    'tuesday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '5:00 PM'],
                    'thursday' => ['09:00 AM', '5:00 PM'],
                ]
            ],
            [
                'id' => 3,
                'name' => 'Dra. Natalia Ramos',
                'role' => 'Medicina fisica y Rehabilitacion',
                'description' => 'La Dra. Ramos cuenta con más de 15 años de experiencia en las áreas de fertilidad y obstetricia.',
                'photo' => [
                    'url' => asset('/images/doctors/doctor-3.webp'),
                    'alt' => 'Dra. CARMEN CARRILLO'
                ],
                'hours' => [
                    'monday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '02:00 PM', '03:00 PM'],
                    'wednesday' => ['09:00 AM', '5:00 PM'],
                    'friday' => ['9:35 AM', '5:35 PM']
                ],
            ],
            [
                'id' => 4,
                'name' => 'Dr. Juan Pérez',
                'role' => 'Especialista en Manejo del Dolor',
                'description' => 'Con más de 15 años de experiencia en el tratamiento del dolor crónico, el Dr. Pérez lidera nuestro equipo con un enfoque centrado en el paciente.',
                'photo' => [
                    'url' => asset('/images/doctors/doctor1.jpg'),
                    'alt' => 'Dr. Juan Pérez'
                ],
                'hours' => [
                    'monday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '02:00 PM', '03:00 PM'],
                    'tuesday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '5:00 PM'],
                    'wednesday' => ['09:00 AM', '5:00 PM'],
                    'thursday' => ['09:00 AM', '5:00 PM'],
                    'friday' => ['09:00 AM', '5:00 PM']
                ]
            ],
            [
                'id' => 5,
                'name' => 'Dra. María Gómez',
                'role' => 'Fisioterapeuta',
                'description' => 'Especialista en rehabilitación y fisioterapia para el manejo del dolor.',
                'photo' => [
                    'url' => asset('/images/doctors/doctor2.png'),
                    'alt' => 'Dra. María Gómez'
                ],
                'hours' => [
                    'monday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '5:00 PM'],
                    'tuesday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '5:00 PM'],
                    'wednesday' => ['09:00 AM', '5:00 PM'],
                    'thursday' => ['9:35 AM', '5:35 PM'],
                    'friday' => ['9:35 AM', '5:35 PM']
                ]
            ],
            [
                'id' => 6,
                'name' => 'Dr. Carlos Rodríguez',
                'role' => 'Anestesiólogo',
                'description' => 'Experto en técnicas de anestesia y procedimientos intervencionistas para el alivio del dolor.',
                'photo' => [
                    'url' => asset('/images/doctors/doctor3.png'),
                    'alt' => 'Dr. Carlos Rodríguez'
                ],
                'hours' => [
                    'monday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '5:00 PM'],
                    'tuesday' => ['09:00 AM', '10:00 AM', '11:00 AM', '01:00 PM', '5:00 PM'],
                    'wednesday' => ['9:35 AM', '5:35 PM'],
                    'thursday' => ['9:35 AM', '5:35 PM'],
                    'friday' => ['9:35 AM', '5:35 PM']
                ]
            ]
        ]
    ])

@stop