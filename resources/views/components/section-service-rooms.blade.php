<section id="salas" class="section__rooms">
  <div class="section__rooms--container container">

    <div class="section__rooms--header" data-aos="fade-up">
      <h2 class="section__rooms--title">{{ $title ?? 'Agenda con nosotros' }}</h2>
      <p class="section__rooms--description">{{ $description ?? 'Agenda una cita el día que mejor te convenga' }}</p>
    </div>

    @if(isset($services) && $services->count())
      <div class="section__rooms--grid">
        @foreach($services as $service)
          <div class="section__rooms--item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
            <div class="section__rooms--item-photo">
              <img src="{{ $service->photo_url }}" alt="{{ $service->title }}" loading="lazy" />
            </div>
            <div class="section__rooms--item-body">
              <h3 class="section__rooms--item-title">{{ $service->title }}</h3>
              @if($service->description)
                <p class="section__rooms--item-description">{{ $service->description }}</p>
              @endif

              @if($service->schedules->count())
                <div class="section__rooms--item-schedules">
                  @php
                    $dayLabels = ['monday'=>'Lun','tuesday'=>'Mar','wednesday'=>'Mié','thursday'=>'Jue','friday'=>'Vie','saturday'=>'Sáb','sunday'=>'Dom'];
                  @endphp
                  @foreach($service->schedules as $schedule)
                    <span class="section__rooms--schedule-badge">
                      <span class="material-symbols-outlined">schedule</span>
                      {{ $dayLabels[$schedule->day] ?? $schedule->day }}
                      {{ \Carbon\Carbon::parse($schedule->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($schedule->end_time)->format('g:i A') }}
                      {{-- @if($schedule->capacity > 1)
                        <span class="section__rooms--capacity">
                          <span class="material-symbols-outlined">group</span>{{ $schedule->capacity }}
                        </span>
                      @endif --}}
                    </span>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif

    {{-- boton --}}
    <div class="section__rooms--footer" data-aos="fade-up" data-aos-delay="200">
      <a href="{{ $button_url ?? '/reservar-servicio' }}" class="button__secondary" style="margin: 40px auto 0">{{ $button_text ?? 'Agendar cita' }}</a>
    </div>
  </div>
</section>
