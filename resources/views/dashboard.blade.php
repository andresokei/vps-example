@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
  <h1 class="page-title">Dashboard</h1>
  <p class="page-subtitle">Gestiona tus grupos, tests y análisis sociométricos.</p>
</div>

<div class="row g-4">

  {{-- Gestión de Grupos --}}
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title">
          <i class="bi bi-people me-2 text-primary"></i>Gestión de Grupos
        </h5>
      </div>
      <div class="card-body">
        <livewire:group-manager />
      </div>
    </div>
  </div>

  {{-- Asignar Tests --}}
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title">
          <i class="bi bi-clipboard-plus me-2 text-primary"></i>Asignar Tests a Grupos
        </h5>
      </div>
      <div class="card-body">
        <livewire:assign-tests />
      </div>
    </div>
  </div>

  {{-- Tests Asignados --}}
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title">
          <i class="bi bi-table me-2 text-primary"></i>Tests Asignados
        </h5>
      </div>
      <div class="card-body p-0">
        <livewire:assigned-tests />
      </div>
    </div>
  </div>

</div>
@endsection
