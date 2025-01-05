@extends('layouts.dashboard')

@section('content')
<div class="container mt-4">
    <!-- Título principal -->
    <h2 class="text-center mb-4"><i class="fas fa-chart-bar"></i> Seleccione un grupo y tipo de análisis</h2>

    <!-- Incluir el componente Livewire -->
    @livewire('analisis-selector')
</div>
@endsection
