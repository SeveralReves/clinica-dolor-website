@php
    $logo = asset('/images/logos/logo.svg');

    $menu = [
      [
        'title' => 'Inicio',
        'url' => '/#'
      ],
      [
        'title' => 'Acerca de',
        'url' => '/#'
      ],
      [
        'title' => 'Servicios',
        'url' => '/#'
      ],
      [
        'title' => 'Doctores',
        'url' => '/#'
      ],
      [
        'title' => 'Preguntas Frecuentes',
        'url' => '/#'
      ],
    ];

    $button = [
      'title' => 'Agendar una cita',
      'url' => '/#',
    ]
@endphp

<header class="header">
  <div class="container header__container">
    <a href="/" class="header__logo-link">
      <img src="{{ $logo }}" alt="logo header" title="logo header" loading="lazy" class="header__logo" width="50" height="50">
      <span>Clinica del Dolor Apure</span>
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