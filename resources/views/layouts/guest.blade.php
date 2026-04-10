<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sociogram') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&family=Mulish:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased">
    <div class="min-h-screen flex">

        <!-- Panel izquierdo: marca -->
        <div class="hidden lg:flex lg:w-1/2 flex-col items-center justify-center p-12 text-white"
             style="background: linear-gradient(135deg, #1a21c8 0%, #2937f0 45%, #9f1ae2 100%);">
            <a href="/" class="mb-8 opacity-90 hover:opacity-100 transition-opacity">
                <img src="{{ asset('assets/img/logo.png') }}" alt="Sociogram Logo" class="w-20 h-auto mx-auto">
            </a>
            <h1 class="text-5xl font-bold mb-4 tracking-tight" style="font-family: 'Kanit', sans-serif;">
                Sociogram
            </h1>
            <p class="text-lg text-white/75 text-center max-w-xs leading-relaxed">
                Comprende las dinámicas sociales de tu aula como nunca antes.
            </p>
            <div class="mt-12 grid grid-cols-1 gap-3 w-full max-w-xs">
                <div class="flex items-center gap-3 text-white/70 text-sm">
                    <span class="text-white/90">✓</span> Sociogramas visuales en segundos
                </div>
                <div class="flex items-center gap-3 text-white/70 text-sm">
                    <span class="text-white/90">✓</span> Análisis de cohesión y reciprocidad
                </div>
                <div class="flex items-center gap-3 text-white/70 text-sm">
                    <span class="text-white/90">✓</span> Exporta en PDF y CSV
                </div>
            </div>
        </div>

        <!-- Panel derecho: formulario -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">

                <!-- Logo para móvil -->
                <div class="mb-8 lg:hidden text-center">
                    <a href="/">
                        <img src="{{ asset('assets/img/logo.png') }}" alt="Sociogram Logo" class="w-12 h-auto mx-auto mb-3">
                    </a>
                    <p class="text-lg font-bold text-gray-800" style="font-family: 'Kanit', sans-serif;">Sociogram</p>
                </div>

                {{ $slot }}

                <p class="mt-6 text-center text-sm text-gray-400">
                    <a href="/" class="hover:text-gray-600 transition-colors">← Volver al inicio</a>
                </p>
            </div>
        </div>

    </div>
</body>

</html>
