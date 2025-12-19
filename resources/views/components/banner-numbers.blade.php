<div class="banner__numbers">
    <div class="banner__numbers--container container">
        @foreach ($numbers as $number)
            <div class="banner__numbers--item">
                <span class="banner__numbers--value">{{ $number['value'] }}</span>
                <span class="banner__numbers--label">{{ $number['label'] }}</span>
            </div>
            @if(!$loop->last)
                <div class="banner__number--divider"></div>
            @endif
        @endforeach
    </div>
</div>