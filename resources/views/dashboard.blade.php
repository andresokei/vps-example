<!-- resources/views/dashboard.blade.php -->
@extends('layouts.dashboard')

@section('content')
<!-- Begin Page Content -->
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>

    <!-- Content Row -->
    <div class="row">
        <!-- Componente de gestión de grupos -->
        <div class="col-md-4 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Gestión de Grupos</h6>
                </div>
                <div class="card-body">
                    <livewire:group-manager />
                </div>
            </div>
        </div>

        <!-- Componente de asignación de tests -->
        <div class="col-md-8 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Tests</h6>
                </div>
                <div class="card-body">
                    <livewire:tests />
                </div>
            </div>
        </div>
    </div>

    <!-- Resumen rápido o estadísticas clave si lo deseas -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Actividad Reciente</h6>
                </div>
                <div class="card-body">
                    <!-- Aquí podrías añadir una lista simple de actividades recientes -->
                    <p class="text-center">No hay actividades recientes para mostrar.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Dashboard cargado correctamente');
    });
</script>
@endpush