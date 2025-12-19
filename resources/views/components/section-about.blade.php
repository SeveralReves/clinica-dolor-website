<section class="section__about">
    <div class="container section__about--container">
        <div class="section__about--content">
            <span class="section__about--pretitle">{{ $pretitle }}</span>
            <h2 class="section__about--title">{{ $title }}</h2>
            <div class="section__about--description">{!! $description !!}</div>
            <a href="{{ $button['url'] }}" class="button__primary section__about--button">
                {{ $button['title'] }}
            </a>
        </div>
        <div class="section__about--image">
            <img src="{{ $image['url'] }}" alt="{{ $image['alt'] }}">
        </div>
    </div>
</section>