<!-- resources/views/layouts/custom.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Incluir Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS desde CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">

    <!-- Include styles and scripts -->
    <!-- Custom fonts for this template-->
    <link href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <style>
        body {
            background-color: #e0f2ff !important;
            /* Forzamos el azul suave */
            font-family: 'Nunito', sans-serif;
        }
    </style>
    <!-- Custom styles for this template-->
    <link href="{{ asset('css/sb-admin-2.min.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
</head>

<body style="margin: 0; padding: 0; min-height: 100vh; display: flex; flex-direction: column;">
    <header>
        <!-- Custom navigation bar -->
    </header>

    <main style="flex-grow: 1; display: flex; justify-content: center; align-items: center;">
        @yield('content') <!-- Aquí es donde el contenido dinámico será cargado -->
    </main>

    <footer>
        <!-- Custom footer -->
    </footer>

    <!-- Bootstrap JS desde CDN -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/js/bootstrap.bundle.min.js"></script>

    @livewireScripts
</body>

</html>
