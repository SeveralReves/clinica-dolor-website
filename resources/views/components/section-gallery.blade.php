<section id="galeria" class="section__gallery">
  <div class="section__gallery--container container">

    <div class="section__gallery--header" data-aos="fade-up">
      <h2 class="section__gallery--title">{{ $title ?? 'Nuestra Galería' }}</h2>
      <p class="section__gallery--description">{{ $description ?? 'Conoce nuestras instalaciones y momentos especiales.' }}</p>
    </div>

    @if(isset($items) && $items->count())
      <div class="section__gallery--grid" data-aos="fade-up" data-aos-delay="100">
        @foreach($items as $item)
          @if($item->type === 'photo')
            <a
              href="{{ $item->media_url }}"
              class="section__gallery--item glightbox"
              data-gallery="gallery-main"
              data-title="{{ $item->title }}"
              data-description="{{ $item->description }}"
            >
              <img src="{{ $item->thumb_url }}" alt="{{ $item->title ?? 'Galería' }}" loading="lazy" />
              <div class="section__gallery--overlay">
                <span class="material-symbols-outlined">zoom_in</span>
              </div>
            </a>
          @else
            <a
              href="{{ $item->video_url }}"
              class="section__gallery--item glightbox"
              data-gallery="gallery-main"
              data-type="video"
              data-title="{{ $item->title }}"
              data-description="{{ $item->description }}"
            >
              <img src="{{ $item->thumb_url }}" alt="{{ $item->title ?? 'Video' }}" loading="lazy" />
              <div class="section__gallery--overlay section__gallery--overlay-video">
                <span class="material-symbols-outlined">play_circle</span>
              </div>
            </a>
          @endif
        @endforeach
      </div>
    @endif

  </div>
</section>
