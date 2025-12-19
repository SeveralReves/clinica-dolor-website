<div class="banner__simple">
  <div class="banner__simple--container container">
    <div class="banner__simple--content">
        <h2 class="banner__simple--title">{{ $title }}</h2>
        <p class="banner__simple--description">{{ $description }}</p>
        @if(isset($button))
            <a href="{{ $button['url'] }}" class="banner__simple--button button__secondary">
                {{ $button['title'] }}
            </a>
        @endif
    </div>
  </div>
</div>