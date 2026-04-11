@extends('layouts.dashboard')

@section('title', __('My Groups'))

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
  <div>
    <h1 class="page-title">{{ __('My Groups') }}</h1>
    <p class="page-subtitle">{{ __('Manage student groups.') }}</p>
  </div>
  <a href="{{ route('grupos.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> {{ __('New Group') }}
  </a>
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th>
          <th>{{ __('Group Name') }}</th>
          <th>{{ __('Students') }}</th>
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
              {{ __('No groups created. Create the first one from the Dashboard.') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
