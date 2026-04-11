@extends('layouts.dashboard')

@section('title', 'Dashboard')

@section('content')
<div class="page-header">
  <h1 class="page-title">{{ __('Dashboard') }}</h1>
  <p class="page-subtitle">{{ __('Manage your groups, tests and sociometric analyses.') }}</p>
</div>

@if (!Auth::user()->onboarding_dismissed_at)
  <livewire:onboarding-checklist />
@endif

<div class="row g-4">

  {{-- Gestión de Grupos --}}
  <div class="col-lg-4" id="group-manager">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title">
          <i class="bi bi-people me-2 text-primary"></i>{{ __('Group Management') }}
        </h5>
      </div>
      <div class="card-body">
        <livewire:group-manager />
      </div>
    </div>
  </div>

  {{-- Asignar Tests --}}
  <div class="col-lg-8" id="assign-tests">
    <div class="card h-100">
      <div class="card-header">
        <h5 class="card-title">
          <i class="bi bi-clipboard-plus me-2 text-primary"></i>{{ __('Assign Tests to Groups') }}
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
          <i class="bi bi-table me-2 text-primary"></i>{{ __('Assigned Tests') }}
        </h5>
      </div>
      <div class="card-body p-0">
        <livewire:assigned-tests />
      </div>
    </div>
  </div>

</div>
@endsection
