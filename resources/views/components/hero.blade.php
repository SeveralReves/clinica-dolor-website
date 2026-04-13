@php
  $image = asset('/images/hero.jpg');
    
@endphp

<div id="home" class="hero">
  <div class="hero__container container">
    <div class="hero__content ">
        @if (isset($title) && !empty($title))
          <h1 class="hero__title" data-aos="fade-up" data-aos-duration="1500">
            {!! $title  !!}
          </h1>
        @endif
        @if (isset($description) && !empty($description))
          <p class="hero__description lead" data-aos="fade-up" data-aos-delay="300" data-aos-duration="1500">
            {{ $description }}
          </p>  
        @endif
        @if (isset($button['url']) && !empty($button['url']))
          <div class="hero__buttons" data-aos="fade-up" data-aos-delay="500" data-aos-duration="1500">
              <a href="{{ $button['url'] }}" title="{{ $button['title'] ?? 'Ver más' }}" class="hero__button button__primary" >
                {{ $button['title'] ?? 'Ver más' }}
              </a>
              @if (isset($button_secondary['title']) && !empty($button_secondary['title']))
                <a href="{{ $button_secondary['url'] }}" title="{{ $button_secondary['title'] ?? 'Ver más' }}" class="hero__button button__secondary" >
                  {{ $button_secondary['title'] ?? 'Ver más' }}
                </a>
              @endif
          </div>
        @endif
    </div>
    <div class="hero__picture"  data-aos="fade-up" data-aos-duration="1500">
      <div class="hero__overlay"></div>
      <div class="hero__card">
        <div class="hero__card--images">
          @foreach ($cards['items'] as $item)
            <img src="{{ $item['url'] }}" alt="{{ $item['alt'] }}" loading="lazy" width="40" height="40">
          @endforeach
        </div>
        <div class="hero__card--content">
          @if (isset($cards['title']) && !empty($cards['title']))
            <p class="hero__card--text">{{ $cards['title'] }}</p>
          @endif
          @if (isset($cards['subtitle']) && !empty($cards['subtitle']))
            <p class="hero__card--subtext">{{ $cards['subtitle'] }}</p>
          @endif
        </div>
      </div>
      <img src="{{ $image }}" alt="golden hero moving" title="golden hero moving"  loading="lazy" class="hero__image">
    </div>
  </div>
</div>