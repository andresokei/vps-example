@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
  <h1 class="dashboard-title">Dashboard</h1>

  {{-- Fila 1: dos cards side-by-side --}}
  <div class="row">
    <div class="col-lg-4 mb-4">
      <div class="card-custom p-4 h-100">
        <div class="card-header">
          <h5>Gestión de Grupos</h5>
        </div>
        <div class="card-body p-0 mt-3">
          <livewire:group-manager />
        </div>
      </div>
    </div>

    <div class="col-lg-8 mb-4">
      <div class="card-custom p-4 h-100">
        <div class="card-header">
          <h5>Asignar Tests a Grupos</h5>
        </div>
        <div class="card-body mt-3">
          <livewire:assign-tests />
        </div>
      </div>
    </div>
  </div>

  {{-- Fila 2: card ancho completo --}}
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card-custom p-4">
        <div class="card-header">
          <h5>Tests Asignados</h5>
        </div>
        <div class="card-body mt-3">
          <livewire:assigned-tests />
        </div>
      </div>
    </div>
  </div>
</div>
@endsection
