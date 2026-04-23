<section class="section__thanks">
    <div class="container section__thanks--container">
      <div class="section__thanks--card">
        <i class="section__thanks--icon bx bx-check"></i>
        <h1 class="section__thanks--title">{{ $title }}</h1>
        <h2 class="section__thanks--subtitle">{!! $subtitle !!}</h2>

        <div class="section__thanks--content">
          <div class="section__thanks--header">
            <div class="section__thanks--header-item">
              <h3 class="section__thanks--header-title">Referencia</h3>
              <p class="section__thanks--header-info">{{ $id }}</p>
            </div>
            <div class="section__thanks--header-item">
              <h3 class="section__thanks--header-title">Estado</h3>
              <p class="section__thanks--header-info">
                @if($status === 'confirmed') Confirmada
                @elseif($status === 'pending') Pendiente de confirmación
                @elseif($status === 'cancelled') Cancelada
                @elseif($status === 'completed') Completada
                @else Desconocido
                @endif
              </p>
            </div>
          </div>

          <div class="section__thanks--specialist">
            @if($service['photo_url'])
              <img src="{{ $service['photo_url'] }}" alt="{{ $service['title'] }}" class="section__thanks--specialist-photo">
            @endif
            <div class="section__thanks--specialist-info">
              <h3 class="section__thanks--specialist-name">{{ $service['title'] }}</h3>
              @if($service['description'])
                <p class="section__thanks--specialist-role">{{ $service['description'] }}</p>
              @endif
            </div>
          </div>

          <div class="section__thanks--details">
            <h3 class="section__thanks--header-title">Detalles de la reserva</h3>
            <p class="section__thanks--details-info">
              <strong>Fecha:</strong> {{ $date }}<br>
              <strong>Hora:</strong> {{ $hour }}<br>
              <strong>Teléfono:</strong> {{ $phone }}<br>
              <strong>Email:</strong> {{ $email }}<br>
              @if($notes)
                <strong>Notas:</strong> {{ $notes }}<br>
              @endif
            </p>
          </div>

          <div class="section__thanks--buttons">
            <a href="/reservar-servicio" class="section__thanks--button button__primary">Nueva Reserva</a>
            <a href="/" class="section__thanks--button button__secondary">Ir a Inicio</a>
          </div>
        </div>
      </div>
    </div>
</section>
