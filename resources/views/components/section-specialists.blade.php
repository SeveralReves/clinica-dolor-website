<section class="section__specialists">
  <div class="section__specialists--container container">
    <div class="section__specialists--header">
      <h2 class="section__specialists--title">{{ $title }}</h2>
      <p class="section__specialists--description">{{ $description }}</p>
    </div>
    @if (isset($specialists) && count($specialists))
      <div class="section__specialists--grid">
        @foreach ($specialists as $specialist)
          <div class="section__specialists--item">
            <div class="section__specialists--item-photo">
              <img src="{{  $specialist->photo_url }}" alt="{{ $specialist['name'] }}">
            </div>
            <h3 class="section__specialists--item-name">{{ $specialist['name'] }}</h3>
            <p class="section__specialists--item-role">{{ $specialist['role'] }}</p>
            <p class="section__specialists--item-description">{{ $specialist['description'] }}</p>
            <div class="section__specialists--item-button">
              <a href="/agendar?specialist={{ $specialist['id'] }}" class="button__primary button__primary--small">Agendar</a>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>
</section>