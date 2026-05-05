@extends('layouts.dashboard')

@section('title', 'Admin - Users')

@section('content')
<div class="page-header">
  <h1 class="page-title">{{ __('Users') }}</h1>
  <p class="page-subtitle">{{ __('Review registered accounts and open the app exactly as a professor sees it.') }}</p>
</div>

@if (session('message'))
  <div class="alert alert-success">{{ session('message') }}</div>
@endif

<div class="card">
  <div class="card-header">
    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-center">
      <div class="col-md-8">
        <label for="q" class="visually-hidden">{{ __('Search users') }}</label>
        <input
          id="q"
          name="q"
          type="search"
          value="{{ $search }}"
          class="form-control"
          placeholder="{{ __('Search by name or email') }}"
        >
      </div>
      <div class="col-md-auto">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-search me-1"></i>{{ __('Search') }}
        </button>
      </div>
      @if ($search !== '')
        <div class="col-md-auto">
          <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">{{ __('Clear') }}</a>
        </div>
      @endif
    </form>
  </div>

  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>{{ __('User') }}</th>
          <th>{{ __('Role') }}</th>
          <th class="text-center">{{ __('Groups') }}</th>
          <th class="text-center">{{ __('Tests') }}</th>
          <th class="text-center">{{ __('Assignments') }}</th>
          <th>{{ __('Registered') }}</th>
          <th class="text-end">{{ __('Actions') }}</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $user)
          <tr>
            <td>
              <div class="fw-semibold text-dark">{{ $user->name }}</div>
              <div class="small text-muted">{{ $user->email }}</div>
            </td>
            <td>
              @forelse ($user->roles as $role)
                <span class="badge text-bg-light border">{{ $role->name }}</span>
              @empty
                <span class="text-muted small">{{ __('No role') }}</span>
              @endforelse
            </td>
            <td class="text-center">{{ $user->grupos_count }}</td>
            <td class="text-center">{{ $user->tests_count }}</td>
            <td class="text-center">{{ $user->asignaciones_test_count }}</td>
            <td>
              <span title="{{ optional($user->created_at)->format('Y-m-d H:i:s') }}">
                {{ optional($user->created_at)->format('d/m/Y') }}
              </span>
            </td>
            <td class="text-end">
              @if (! $user->hasRole('admin') && ! $user->is(auth()->user()))
                <form method="POST" action="{{ route('admin.users.impersonate', $user) }}" class="d-inline">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-eye me-1"></i>{{ __('View as user') }}
                  </button>
                </form>
              @else
                <span class="text-muted small">{{ __('Protected') }}</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="text-center text-muted py-4">{{ __('No users found.') }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  @if ($users->hasPages())
    <div class="card-footer">
      {{ $users->links() }}
    </div>
  @endif
</div>
@endsection
