{{-- resources/views/partials/sidebar.blade.php --}}
@php
  $canUseTeacherTools = auth()->user()?->hasAnyRole(['profesor', 'admin']);
  $canUseAdminTools = auth()->user()?->hasRole('admin');
@endphp

<aside class="app-sidebar" id="appSidebar">

  <a class="sidebar-brand" href="{{ route('dashboard') }}">
    <div class="sidebar-brand-icon">
      <i class="bi bi-diagram-3-fill"></i>
    </div>
    <span class="sidebar-brand-text">Sociogram</span>
  </a>

  <ul class="sidebar-nav">
    <li class="sidebar-section-label">{{ __('Main') }}</li>

    <li>
      <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
         href="{{ route('dashboard') }}">
        <i class="bi bi-house nav-icon"></i>
        {{ __('Dashboard') }}
      </a>
    </li>

    <li>
      <a class="sidebar-link {{ request()->routeIs('feedback.*') ? 'active' : '' }}"
         href="{{ route('feedback.create') }}">
        <i class="bi bi-chat-dots nav-icon"></i>
        {{ __('Feedback') }}
      </a>
    </li>

    @if ($canUseTeacherTools)
      <li class="sidebar-section-label">{{ __('Tools') }}</li>

      <li>
        <a class="sidebar-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}"
           href="{{ route('grupos.index') }}">
          <i class="bi bi-people nav-icon"></i>
          {{ __('Groups') }}
        </a>
      </li>

      <li>
        <a class="sidebar-link {{ request()->routeIs('tests.*') ? 'active' : '' }}"
           href="{{ route('tests.index') }}">
          <i class="bi bi-file-earmark-check nav-icon"></i>
          {{ __('Tests') }}
        </a>
      </li>

      <li>
        <a class="sidebar-link {{ request()->routeIs('analisis*') ? 'active' : '' }}"
           href="{{ route('analisis') }}">
          <i class="bi bi-graph-up nav-icon"></i>
          {{ __('Analysis') }}
        </a>
      </li>
    @endif

    @if ($canUseAdminTools)
      <li class="sidebar-section-label">{{ __('Admin') }}</li>

      <li>
        <a class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
           href="{{ route('admin.dashboard') }}">
          <i class="bi bi-speedometer2 nav-icon"></i>
          {{ __('Overview') }}
        </a>
      </li>

      <li>
        <a class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
           href="{{ route('admin.users.index') }}">
          <i class="bi bi-shield-lock nav-icon"></i>
          {{ __('Users') }}
        </a>
      </li>

      <li>
        <a class="sidebar-link {{ request()->routeIs('admin.feedback.*') ? 'active' : '' }}"
           href="{{ route('admin.feedback.index') }}">
          <i class="bi bi-inbox nav-icon"></i>
          {{ __('Feedback') }}
        </a>
      </li>
    @endif
  </ul>

  <div style="padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.07); flex-shrink:0;">
    <div style="display:flex; align-items:center; gap:0.6rem;">
      <div class="topbar-avatar" style="width:30px;height:30px;font-size:0.72rem;">
        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
      </div>
      <div style="overflow:hidden;">
        <div style="font-size:0.8rem;font-weight:500;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          {{ auth()->user()->name }}
        </div>
        <div style="font-size:0.7rem;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
          {{ auth()->user()->email }}
        </div>
      </div>
    </div>
  </div>

</aside>
