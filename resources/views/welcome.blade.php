@extends('layouts.default')

@section('content')

    @include('components.hero', [
        'title' => 'Recupera tu vida del <span>dolor crónico</span>',
        'description' => 'Tratamientos avanzados y no invasivos diseñados para ayudarle a vivir sin dolor. Experimente el futuro de la medicina regenerativa con nuestro enfoque centrado en la empatía.',
        'button' => [
            'url' => '#',
            'title' => 'Agendar Consulta'
        ],
        'button_secondary' => [
            'url' => '#',
            'title' => 'Leer Más'
        ],
        'cards' => [
            'title' => 'Más de 500 pacientes satisfechos',
            'subtitle' => 'Calificación: 4,9/5 estrellas',
            'items' => [
                [
                    'url' => asset('/images/cards/user-1.png'),
                    'alt' => 'User 1',
                ],
                [
                    'url' => asset('/images/cards/user-2.png'),
                    'alt' => 'User 2',
                ],
                [
                    'url' => asset('/images/cards/user-3.png'),
                    'alt' => 'User 3',
                ],
            ]
        ]
    ])


    @include('components.banner-numbers', [
        'numbers' => [
            [
                'value' => '15+',
                'label' => 'Años de Experiencia'
            ],
            [
                'value' => '500',
                'label' => 'Pacientes Satisfechos'
            ],
            [
                'value' => '4.9',
                'label' => 'Calificación Promedio'
            ],
            [
                'value' => '24/7',
                'label' => 'Soporte al Paciente'
            ]
        ]
    ])
    @include('components.section-booking', [
        'title' => 'Book Your Move Online',
        'description' => 'Get a free quote in just a few simple steps.',
        'button' => [
            'url' => '#booking',
            'title' => 'Book your date now'
        ],
        'wp_action' => 'booking'
    ])

    @include('components.section-faq', [
        'title' => 'Preguntas frecuentes',
        'description' => "¿Tienes preguntas? Tenemos respuestas.",
        'cta' => [
            'text' => 'Contáctanos para más información',
            'url'  => '#booking'
        ],
        'faqs' => [
            [
            'q' => '¿Aceptan mi seguro?',
            'a' => 'Aceptamos la mayoría de los planes de seguro principales. Comuníquese con nuestra oficina con los detalles específicos de su póliza para verificarla.'
            ],
            [
            'q' => '¿Qué debo esperar en mi primera visita?',
            'a' => 'Su primera visita incluirá una evaluación completa, una revisión de su historial médico y, posiblemente, algunas pruebas diagnósticas. Nuestro objetivo es comprender la causa de su dolor.',
            ],
            [
            'q' => '¿Son dolorosos los tratamientos?',
            'a' => 'La mayoría de nuestros tratamientos son mínimamente invasivos y están diseñados para ser lo más cómodos posible. Ofrecemos diversas opciones de sedación y anestesia para garantizar su comodidad.'
            ],
            [
            'q' => '¿Necesito una referencia?',
            'a' => 'Esto depende de tu plan de seguro específico. Los planes PPO generalmente no requieren referencia, mientras que los planes HMO sí.'
            ],
            [
            'q' => '¿Qué es su política sobre reprogramación o cancelación?',
            'a' => 'Puede reprogramar hasta 24 horas antes de la cita sin tarifas. Las cancelaciones tardías pueden estar sujetas a una tarifa.'
            ],
        ]
    ])

    @include('components.banner-simple', [
        'title' => '¿Listo para vivir sin dolor?',
        'description' => 'Agenda tu consulta hoy y da el primer paso hacia una vida más saludable y feliz.',
        'button' => [
            'url' => '#booking',
            'title' => 'Agendar Consulta'
        ]
    ])


{{-- <div class="container">
    <p>This is the user content</p>
    
    <div
        data-vue="ExampleComponent"
        data-props='@json(["postId" => 123, "initial" => false])'>
    </div>

</div> --}}
@stop