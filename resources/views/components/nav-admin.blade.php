<div class="layout__admin--nav">
    <a href="{{ route('dashboard') }}" class="layout__admin--nav-item {{ request()->routeIs('dashboard') ? 'active' : ''}}">
      <span class="material-symbols-outlined">dashboard</span>
      <span>
        Dashboard
      </span>
    </a>
    {{-- @role(['admin', 'superadmin']) --}}
      <a href="{{ route('users') }}" class="layout__admin--nav-item {{ request()->routeIs('users') ? 'active' : ''}}">
        <span class="material-symbols-outlined">people</span>
        <span>
          Usuarios
        </span>
      </a>
    {{-- @endrole --}}
</div>