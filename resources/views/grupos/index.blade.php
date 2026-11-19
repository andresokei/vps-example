@extends('layouts.dashboard')

@section('title', __('My Groups'))

@section('content')
<div class="page-header d-flex justify-content-between align-items-start">
  <div>
    <h1 class="page-title">{{ __('My Groups') }}</h1>
  </div>
  @unless(session()->has('impersonator_id'))
  <a href="{{ route('grupos.create') }}" class="btn btn-primary btn-sm">
    <i class="bi bi-plus-lg me-1"></i> {{ __('New Group') }}
  </a>
  @endunless
</div>

<div class="card">
  <div class="card-body p-0">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>{{ __('Group Name') }}</th>
          <th>{{ __('Students') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse($grupos as $grupo)
          <tr>
            <td class="fw-medium text-break"><a href="{{ route('grupos.show', $grupo) }}">{{ $grupo->nombre_grupo }}</a></td>
            <td>{{ $grupo->estudiantes_count }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="2" class="text-center py-4 text-muted">
              {{ __('No groups created yet.') }}
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
