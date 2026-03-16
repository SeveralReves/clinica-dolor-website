<section class="section__thanks">
    <div class="container section__thanks--container">
      <div class="section__thanks--card">
        <i class="section__thanks--icon bx bx-check"></i>
        <h1 class="section__thanks--title">{{ $title }}</h1>
        <h2 class="section__thanks--subtitle">{!! $subtitle !!}</h2>
        <div class="section__thanks--content">
          <div class="section__thanks--header">
            <div class="section__thanks--header-item">
              <h3 class="section__thanks--header-title">ID</h3>
              <p class="section__thanks--header-info">
                {{ $id }}
              </p>
            </div>
            <div class="section__thanks--header-item">
              <h3 class="section__thanks--header-title">Estado de la cita</h3>
              <p class="section__thanks--header-info">
                @if($status === 'confirmed')
                  Confirmada
                @elseif($status === 'pending')
                  Pendiente
                @elseif($status === 'canceled')
                  Cancelada
                @elseif($status === 'completed')
                  Completada
                @else
                  Desconocido
                @endif
              </p>
            </div>
            
          </div>
          <div class="section__thanks--specialist">
              <img src="{{ $specialist['photo']['url'] }}" alt="{{ $specialist['photo']['alt'] }}" class="section__thanks--specialist-photo">
            <div class="section__thanks--specialist-info">
              <h3 class="section__thanks--specialist-name">{{ $specialist['name'] }}</h3>
              <p class="section__thanks--specialist-role">{{ $specialist['role'] }}</p>
            </div>
          </div>
          <div class="section__thanks--details">
            <h3 class="section__thanks--header-title">Detalles de la cita</h3>
            <p class="section__thanks--details-info">
              <strong>Fecha:</strong> {{ $date }}<br>
              <strong>Hora:</strong> {{ $hour }}<br>
              <strong>Teléfono:</strong> {{ $phone }}<br>
              <strong>Email:</strong> {{ $email }}<br>
              <strong>Motivo:</strong> {{ $reason }}
            </p>
          </div>
          <div class="section__thanks--buttons">
            <a href="/consultar" class="section__thanks--button button__primary">Revisar Estado</a>
            <a href="/" class="section__thanks--button button__secondary">Ir a Inicio</a>
          </div>
        </div>
      </div>
    </div>
</section>