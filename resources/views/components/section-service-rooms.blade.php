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
                @php
                  $dayOrder = ['monday'=>0,'tuesday'=>1,'wednesday'=>2,'thursday'=>3,'friday'=>4,'saturday'=>5,'sunday'=>6];
                  $dayShort = ['monday'=>'Lun','tuesday'=>'Mar','wednesday'=>'Mié','thursday'=>'Jue','friday'=>'Vie','saturday'=>'Sáb','sunday'=>'Dom'];
                  $groups = $service->schedules
                    ->groupBy(fn($s) => $s->start_time . '-' . $s->end_time)
                    ->sortKeys();
                @endphp
                <div class="section__rooms--item-schedules">
                  <p class="section__rooms--schedule-label">
                    <span class="material-symbols-outlined">calendar_month</span>
                    Horario
                  </p>
                  @foreach($groups as $group)
                    @php
                      $sorted = $group->sortBy(fn($s) => $dayOrder[$s->day] ?? 99)->values();
                      $firstDay = $dayShort[$sorted->first()->day] ?? '';
                      $lastDay  = $dayShort[$sorted->last()->day] ?? '';
                      $dayRange = $firstDay === $lastDay ? $firstDay : "$firstDay – $lastDay";
                      $start = \Carbon\Carbon::parse($group->first()->start_time)->format('g:i A');
                      $end   = \Carbon\Carbon::parse($group->first()->end_time)->format('g:i A');
                    @endphp
                    <div class="section__rooms--schedule-row">
                      <span class="section__rooms--schedule-days">{{ $dayRange }}</span>
                      <span class="section__rooms--schedule-time">
                        <span class="material-symbols-outlined">schedule</span>
                        {{ $start }} – {{ $end }}
                      </span>
                    </div>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif

    <div class="section__rooms--footer" data-aos="fade-up" data-aos-delay="200">
      <a href="{{ $button_url ?? '/reservar-servicio' }}" class="button__secondary" style="margin: 40px auto 0">{{ $button_text ?? 'Agendar cita' }}</a>
    </div>
  </div>
</section>
