{{-- resources/views/layouts/dashboard.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Meta y título -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sociogram - Dashboard</title>

    <!-- Iconos y estilos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/analisis.css') }}" rel="stylesheet">

      <!-- Nuevo CSS para Dashboard -->
      <link href="{{ asset('css/dashboard.css') }}" rel="stylesheet">
      
    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body id="page-top">
    <div id="wrapper">
        @include('partials.sidebar')

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                @include('partials.topbar')

                <div class="container-fluid">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- Dependencias JS (jQuery, Bootstrap, SB Admin) -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Chart.js y Matrix plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-chart-matrix@^1.1.0/dist/chartjs-chart-matrix.min.js"></script>

    <!-- Livewire Scripts -->
    @livewireScripts

    <!-- Tu script de análisis (debe cargarse tras Livewire y Chart.js) -->
    <script src="{{ asset('js/analisis.js') }}"></script>

    @stack('scripts')
</body>
</html>
