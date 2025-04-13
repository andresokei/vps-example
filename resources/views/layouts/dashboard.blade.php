<!-- resources/views/layouts/dashboard.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Meta y títulos -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sociogram - Dashboard</title>

    <!-- Estilos y fuentes -->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <!-- Incluye aquí otros estilos necesarios -->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">

    <!-- Livewire Styles -->
    @livewireStyles
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Incluir Sidebar -->
        @include('partials.sidebar')

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Incluir Topbar -->
                @include('partials.topbar')

                <!-- Contenido de la página -->
                <div class="container-fluid">
                    @yield('content') <!-- Aquí se renderizará el contenido de las vistas que extiendan este layout -->
                </div>
                <!-- /.container-fluid -->

                

            </div>
            <!-- End of Main Content -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scripts necesarios -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Incluye aquí otros scripts necesarios -->
    <script src="{{ asset('js/sb-admin-2.min.js') }}"></script>

    <!-- Chart.js - Añadir esta línea -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <!-- Livewire Scripts -->
    @livewireScripts

    @stack('scripts')
</body>

</html>
