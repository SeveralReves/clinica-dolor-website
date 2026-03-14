<div class="layout__admin--nav">
    <a href="{{ route('dashboard') }}" class="layout__admin--nav-item {{ request()->routeIs('dashboard') ? 'active' : ''}}">
      <span class="material-symbols-outlined">dashboard</span>
      <span>
        Dashboard
      </span>
    </a>
    @role(['admin', 'superadmin'])
      <a href="{{ route('specialists') }}" class="layout__admin--nav-item {{ request()->routeIs('specialists') ? 'active' : ''}}">
        <span class="material-symbols-outlined">medical_services</span>
        <span>
          Especialistas
        </span>
      </a>
    @endrole
    @role(['admin', 'superadmin'])
      <a href="{{ route('users') }}" class="layout__admin--nav-item {{ request()->routeIs('users') ? 'active' : ''}}">
        <span class="material-symbols-outlined">people</span>
        <span>
          Usuarios
        </span>
      </a>
    @endrole
</div>