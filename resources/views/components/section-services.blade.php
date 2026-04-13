<section id="servicios" class="section__services">
  <div class="section__services--container container">
    <div class="section__services--header">
      <h2 class="section__services--title">{{$title}}</h2>
      <p class="section__services--description">{{ $description }}</p>
    </div>
    <div class="section__services--grid">
      @foreach ($services as $service)
        <div class="section__services--item">
          <span class="section__services--item-icon">
            <i class="bx {{ $service['icon']  }}"></i>
          </span>
          <h3 class="section__services--item-title">{{ $service['title'] }}</h3>
          <p class="section__services--item-description">{{ $service['description'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>