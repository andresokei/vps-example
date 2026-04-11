{{-- resources/views/partials/topbar.blade.php --}}
<header class="app-topbar">

  <button class="topbar-toggle" id="sidebarToggle" type="button" aria-label="Toggle sidebar">
    <i class="bi bi-list"></i>
  </button>

  <div class="topbar-spacer"></div>

  {{-- Language Switcher --}}
  <div class="d-flex align-items-center me-3">
    @foreach(['es' => 'ES', 'en' => 'EN'] as $lang => $label)
      <form action="{{ route('locale.switch') }}" method="POST" style="display:inline;">
        @csrf
        <input type="hidden" name="locale" value="{{ $lang }}">
        <button type="submit"
          class="lang-text-btn {{ app()->getLocale() === $lang ? 'lang-text-btn--active' : 'lang-text-btn--inactive' }}">
          {{ $label }}
        </button>
      </form>
      @if(!$loop->last)<span class="lang-sep">|</span>@endif
    @endforeach
  </div>

  <div class="dropdown">
    <button class="topbar-user-btn"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false">
      <div class="topbar-avatar">
        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
      </div>
      <span class="topbar-username d-none d-sm-block">
        {{ auth()->user()->name }}
      </span>
      <i class="bi bi-chevron-down" style="font-size:0.7rem;color:#94a3b8;"></i>
    </button>

    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="min-width:200px;border-color:#e2e8f0;border-radius:10px;">
      <li>
        <div class="px-3 py-2">
          <div style="font-size:0.82rem;font-weight:600;color:#0f172a;">{{ auth()->user()->name }}</div>
          <div style="font-size:0.75rem;color:#64748b;">{{ auth()->user()->email }}</div>
        </div>
      </li>
      <li><hr class="dropdown-divider my-1"></li>
      <li>
        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
          <i class="bi bi-person" style="font-size:0.9rem;"></i>
          {{ __('My Profile') }}
        </a>
      </li>
      <li><hr class="dropdown-divider my-1"></li>
      <li>
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="dropdown-item d-flex align-items-center gap-2 text-danger">
            <i class="bi bi-box-arrow-right" style="font-size:0.9rem;"></i>
            {{ __('Log out') }}
          </button>
        </form>
      </li>
    </ul>
  </div>

</header>
