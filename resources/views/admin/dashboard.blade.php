@extends('layouts.dashboard')

@section('title', 'Admin - Overview')

@section('content')
<div class="page-header d-flex flex-column flex-md-row justify-content-between gap-3">
  <div>
    <h1 class="page-title">{{ __('Admin overview') }}</h1>
    <p class="page-subtitle">{{ __('A quick read on registrations, usage and recent classroom activity.') }}</p>
  </div>
  <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary align-self-md-start">
    <i class="bi bi-people me-1"></i>{{ __('Manage users') }}
  </a>
</div>

<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="admin-stat">
      <div class="admin-stat-icon text-bg-primary"><i class="bi bi-people"></i></div>
      <div>
        <div class="admin-stat-label">{{ __('Users') }}</div>
        <div class="admin-stat-value">{{ number_format($totals['users']) }}</div>
        <div class="admin-stat-meta">+{{ number_format($totals['newUsers7Days']) }} {{ __('last 7 days') }}</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="admin-stat">
      <div class="admin-stat-icon text-bg-success"><i class="bi bi-person-check"></i></div>
      <div>
        <div class="admin-stat-label">{{ __('Verified') }}</div>
        <div class="admin-stat-value">{{ number_format($totals['verifiedUsers']) }}</div>
        <div class="admin-stat-meta">{{ number_format($totals['professors']) }} {{ __('professors') }}</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="admin-stat">
      <div class="admin-stat-icon text-bg-info"><i class="bi bi-layers"></i></div>
      <div>
        <div class="admin-stat-label">{{ __('Content') }}</div>
        <div class="admin-stat-value">{{ number_format($totals['groups'] + $totals['tests']) }}</div>
        <div class="admin-stat-meta">{{ $totals['groups'] }} {{ __('groups') }} · {{ $totals['tests'] }} {{ __('tests') }}</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="admin-stat">
      <div class="admin-stat-icon text-bg-warning"><i class="bi bi-chat-square-text"></i></div>
      <div>
        <div class="admin-stat-label">{{ __('Responses') }}</div>
        <div class="admin-stat-value">{{ number_format($totals['responses']) }}</div>
        <div class="admin-stat-meta">+{{ number_format($totals['responses7Days']) }} {{ __('last 7 days') }}</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4">
  <div class="col-xl-4">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title"><i class="bi bi-clipboard-data me-2 text-primary"></i>{{ __('Assignments') }}</h5>
      </div>
      <div class="card-body">
        @php
          $statusLabels = ['pendiente' => __('Pending'), 'en progreso' => __('In progress'), 'aplicado' => __('Completed')];
          $statusClasses = ['pendiente' => 'bg-secondary', 'en progreso' => 'bg-warning', 'aplicado' => 'bg-success'];
          $assignmentTotal = max(1, $totals['assignments']);
        @endphp
        @foreach ($statusLabels as $status => $label)
          @php
            $count = (int) ($assignmentStatusCounts[$status] ?? 0);
            $percent = round(($count / $assignmentTotal) * 100);
          @endphp
          <div class="admin-progress-row">
            <div class="d-flex justify-content-between mb-1">
              <span>{{ $label }}</span>
              <strong>{{ $count }}</strong>
            </div>
            <div class="progress" style="height: 8px;">
              <div class="progress-bar {{ $statusClasses[$status] }}" style="width: {{ $percent }}%"></div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  <div class="col-xl-8">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title"><i class="bi bi-clock-history me-2 text-primary"></i>{{ __('Recent assignments') }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead>
            <tr>
              <th>{{ __('Test') }}</th>
              <th>{{ __('Professor') }}</th>
              <th>{{ __('Group') }}</th>
              <th class="text-center">{{ __('Responses') }}</th>
              <th>{{ __('Status') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentAssignments as $assignment)
              <tr>
                <td class="fw-semibold">{{ $assignment->test?->nombre_test ?? __('Untitled') }}</td>
                <td>
                  <div>{{ $assignment->profesor?->name ?? __('Unknown') }}</div>
                  <div class="small text-muted">{{ $assignment->profesor?->email }}</div>
                </td>
                <td>{{ $assignment->grupo?->nombre_grupo ?? __('Unknown') }}</td>
                <td class="text-center">{{ $assignment->respuestas_count }}</td>
                <td><span class="badge text-bg-light border">{{ $assignment->estado }}</span></td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No assignments yet.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-7">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title"><i class="bi bi-person-plus me-2 text-primary"></i>{{ __('Recent users') }}</h5>
      </div>
      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead>
            <tr>
              <th>{{ __('User') }}</th>
              <th>{{ __('Role') }}</th>
              <th class="text-center">{{ __('Activity') }}</th>
              <th>{{ __('Registered') }}</th>
            </tr>
          </thead>
          <tbody>
            @forelse ($recentUsers as $user)
              <tr>
                <td>
                  <div class="fw-semibold">{{ $user->name }}</div>
                  <div class="small text-muted">{{ $user->email }}</div>
                </td>
                <td>
                  @forelse ($user->roles as $role)
                    <span class="badge text-bg-light border">{{ $role->name }}</span>
                  @empty
                    <span class="small text-muted">{{ __('No role') }}</span>
                  @endforelse
                </td>
                <td class="text-center">{{ $user->grupos_count + $user->tests_count + $user->asignaciones_test_count }}</td>
                <td>{{ optional($user->created_at)->format('d/m/Y') }}</td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No users yet.') }}</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title"><i class="bi bi-activity me-2 text-primary"></i>{{ __('Most active professors') }}</h5>
      </div>
      <div class="card-body">
        @forelse ($activeProfessors as $professor)
          <div class="admin-list-row">
            <div>
              <div class="fw-semibold">{{ $professor->name }}</div>
              <div class="small text-muted">{{ $professor->email }}</div>
            </div>
            <div class="text-end small">
              <div>{{ $professor->grupos_count }} {{ __('groups') }}</div>
              <div class="text-muted">{{ $professor->tests_count }} {{ __('tests') }} · {{ $professor->asignaciones_test_count }} {{ __('assignments') }}</div>
            </div>
          </div>
        @empty
          <div class="text-center text-muted py-4">{{ __('No professor activity yet.') }}</div>
        @endforelse
      </div>
    </div>
  </div>

  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h5 class="card-title"><i class="bi bi-exclamation-circle me-2 text-primary"></i>{{ __('Users without activity') }}</h5>
      </div>
      <div class="card-body">
        @forelse ($inactiveUsers as $user)
          <div class="admin-list-row">
            <div>
              <div class="fw-semibold">{{ $user->name }}</div>
              <div class="small text-muted">{{ $user->email }}</div>
            </div>
            <div class="small text-muted">{{ __('Registered') }} {{ optional($user->created_at)->format('d/m/Y') }}</div>
          </div>
        @empty
          <div class="text-muted">{{ __('No inactive users older than one day.') }}</div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
