@extends('layouts.default')

@section('content')

    @include('components.hero', [
        'title' => 'Recupera tu vida del <span>dolor crónico</span>',
        'description' => 'Clínica del Dolor Apure esta disenada para atender pacientes con síndromes dolorosos, única en el Estado Apure. Contamos con un equipo de expertos médicos altamente capacitados, instalaciones modernas y un compromiso inquebrantable con el bienestar de nuestros pacientes.',
        'button' => [
            'url' => '/agendar',
            'title' => 'Agendar Consulta'
        ],
        'button_secondary' => [
            'url' => '#',
            'title' => 'Más información'
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

    @include('components.section-about', [
        'pretitle' => 'Acerca de Nuestra Clínica',
        'title' => 'Sanación con empatía y tecnología avanzada',
        'description' => '<p>La Clínica del Dolor Apure se establece como un centro especializado en el diagnóstico y tratamiento del dolor, con un enfoque multidisciplinario que busca mejorar la calidad de vida de los pacientes. Integrando diversas especialidades para ofrecer un tratamiento integral, atendiendo especialmente aquellos pacientes con patologías agudas y crónicas, oncológicas y no oncológicas. Su objetivo es ofrecer un manejo integral del dolor, combinando recursos médicos y terapéuticos para facilitar la recuperación y reintegración laboral de los pacientes.</p><p>Esta clínica es esencial para mejorar la calidad de vida de los pacientes, considerando aspectos médicos, psicológicos y sociales, garantizando un acceso efectivo a tratamientos especializados.</p><p>El manejo efectivo del dolor requiere la integración de diversas especialidades médicas. La clínica enfatiza la importancia de:
            <ul>
                <li>Un diagnóstico adecuado que permita un tratamiento efectivo.</li>
                <li>La prevención de complicaciones asociadas al dolor crónico.</li>
                <li>La evaluación continua de la calidad de vida del paciente.</li>
            </ul></p>
        <p>En resumen, la Clínica del Dolor Apure es fundamental para abordar el complejo fenómeno del dolor crónico, proporcionando un enfoque integral y multidisciplinario que busca mejorar tanto la salud física como la calidad de vida de los pacientes.</p>',
        'image' => [
            'url' => asset('/images/About-medicine.webp'),
            'alt' => 'About our clinic'
        ],
        'button' => [
            'url' => '#',
            'title' => 'Conoce al Equipo'
        ]
    ])

    @include('components.section-services', [
        'title' => 'Nuestros Servicios',
        'description' => 'Ofrecemos una amplia gama de servicios especializados para el manejo del dolor crónico.',
        'services' => [
            [
                'title' => 'Consultas Medicas Especializadas',
                'description' => 'Evaluaciones exhaustivas para identificar la causa del dolor y desarrollar un plan de tratamiento personalizado.',
                'icon' => 'bx-user-check',
            ],
            [
                'title' => 'Procedimientos quirúrgicos e Intervencionistas',
                'description' => 'Técnicas mínimamente invasivas para aliviar el dolor, incluyendo bloqueos nerviosos y terapias de radiofrecuencia.',
                'icon' => 'bx-plus-medical',
            ],
            [
                'title' => 'Rehability Medical',
                'description' => 'Rehabilitación y ejercicios personalizados para restaurar la movilidad y fuerza.',
                'icon' => 'bx-body',
            ],
            [
                'title' => 'Gym Medical',
                'description' => 'Programas de ejercicio supervisados para mejorar la condición física y reducir el dolor.',
                'icon' => 'bx-dumbbell',
            ],
            [
                'title' => 'Spa Medical',
                'description' => 'Terapias de relajación y bienestar para complementar el tratamiento del dolor.',
                'icon' => 'bx-spa',
            ],
            [
                'title' => 'Educacion',
                'description' => 'Programas educativos para informar sobre el manejo del dolor.',
                'icon' => 'bx-book'
            ]
        ]
    ])

    @include('components.section-specialists', [
        'title' => 'Conoce a Nuestros Especialistas',
        'description' => 'Un equipo dedicado de profesionales comprometidos con tu bienestar.',
        'specialists' => [
            [
                'id' => 1,
                'name' => 'Dra. CARMEN CARRILLO',
                'role' => 'Medicina del Dolor - Anestesiólogo',
                'description' => 'Con más de una década de experiencia, la Dra. Carrillo es la especialista experta en Medicina del Dolor.',
                'photo' => [
                    'url' => asset('/images/doctors/doctor-1.webp'),
                    'alt' => 'Dra. CARMEN CARRILLO'
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
                ]
            ],
            [
                'id' => 4,
                'name' => 'Dr. Juan Pérez',
                'role' => 'Especialista en Manejo del Dolor',
                'description' => 'Con más de 15 años de experiencia en el tratamiento del dolor crónico, el Dr. Pérez lidera nuestro equipo con un enfoque centrado en el paciente.',
                'photo' => [
                    'url' => asset('/images/doctors/doctor1.jpg'),
                    'alt' => 'Dr. Juan Pérez'
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
                ]
            ]
        ]
    ])

    {{-- @include('components.section-booking', [
        'title' => 'Book Your Move Online',
        'description' => 'Get a free quote in just a few simple steps.',
        'button' => [
            'url' => '#booking',
            'title' => 'Book your date now'
        ],
        'wp_action' => 'booking'
    ]) --}}

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
            'url' => '/agendar',
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