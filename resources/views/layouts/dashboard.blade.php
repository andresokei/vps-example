{{-- resources/views/layouts/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Sociogram - @yield('title', 'Dashboard')</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
  <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
  <link href="{{ asset('css/analisis.css') }}" rel="stylesheet">

  @livewireStyles
</head>

<body>
  <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

  @include('partials.sidebar')
  @include('partials.topbar')

  <div class="main-wrapper">
    <div class="page-content">
      @yield('content')
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@^1.1.0/dist/chartjs-chart-matrix.min.js"></script>

  @livewireScripts

  <script src="{{ asset('js/analisis/shared.js') }}"></script>
  <script src="{{ asset('js/analisis/sociograma.js') }}"></script>
  <script src="{{ asset('js/analisis.js') }}"></script>

  <script>
    (function () {
      const toggle = document.getElementById('sidebarToggle');
      const sidebar = document.getElementById('appSidebar');
      const backdrop = document.getElementById('sidebarBackdrop');

      function openSidebar() {
        sidebar?.classList.add('show');
        backdrop?.classList.add('show');
        document.body.style.overflow = 'hidden';
      }

      function closeSidebar() {
        sidebar?.classList.remove('show');
        backdrop?.classList.remove('show');
        document.body.style.overflow = '';
      }

      toggle?.addEventListener('click', openSidebar);
      backdrop?.addEventListener('click', closeSidebar);
    })();
  </script>

  @stack('scripts')
</body>
</html>
