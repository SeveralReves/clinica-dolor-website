<div class="schedule">
  <div class="schedule__container container">
    <div class="schedule__content">
      <h2 class="schedule__title">{!! $title !!}</h2>
      <p class="schedule__subtitle">{{ $subtitle }}</p>
      <div
        data-vue="ServiceBookingForm"
        data-props='@json(["services" => $services])'>
      </div>
    </div>
  </div>
</div>
