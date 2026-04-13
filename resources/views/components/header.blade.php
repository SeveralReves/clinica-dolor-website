@php
    $logo = asset('/images/logos/logo.svg');

    $menu = [
      [
        'title' => 'Inicio',
        'url' => '/#home'
      ],
      [
        'title' => 'Acerca de',
        'url' => '/#acerca-de'
      ],
      [
        'title' => 'Servicios',
        'url' => '/#servicios'
      ],
      [
        'title' => 'Médicos',
        'url' => '/#medicos'
      ],
      [
        'title' => 'Preguntas Frecuentes',
        'url' => '/#preguntas-frecuentes'
      ],
    ];

    $button = [
      'title' => 'Agendar una cita',
      'url' => '/agendar',
    ]
@endphp

<header class="header">
  <div class="container header__container">
    <a href="/" class="header__logo-link">
      <img src="{{ $logo }}" alt="logo header" title="logo header" loading="lazy" class="header__logo" width="50" height="50">
      <span>Clínica del Dolor Apure</span>
    </a>
    <div class="header__content">
      <nav class="header__nav">
        <ul class="header__ul">
          @foreach ($menu as $item)
            <li class="header__li">
              <a href="{{ $item['url'] }}" title="{{ $item['title'] }}" class="header__link">{{ $item['title'] }}</a>
            </li>
          @endforeach
        </ul>
      </nav>
      <a href="{{ $button['url'] }}" title="{{ $button['title'] }}" class="button__primary">
        {{ $button['title'] }}
      </a>
    </div>
  </div>
</header>