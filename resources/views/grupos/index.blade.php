@extends('layouts.dashboard')

@section('title', 'Grupos')

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
  <div>
    <h1 class="page-title">Mis Grupos</h1>
    <p class="page-subtitle">Gestiona los grupos de alumnos.</p>
  </div>
  <a href="{{ route('grupos.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> Nuevo Grupo
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>Nombre del Grupo</th>
          <th>Alumnos</th>
        </tr>
      </thead>
      <tbody>
        @forelse($grupos as $grupo)
          <tr>
            <td class="text-muted">{{ $loop->iteration }}</td>
            <td class="fw-medium">{{ $grupo->nombre_grupo }}</td>
            <td>{{ $grupo->estudiantes_count ?? $grupo->estudiantes->count() }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="3" class="text-center py-4 text-muted">
              No hay grupos creados. Crea el primero desde el Dashboard.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
