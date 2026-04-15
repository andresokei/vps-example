@extends('layouts.dashboard')

@section('title', __('My Tests'))

@section('content')
<div class="container-fluid">
  <div class="page-header mb-4">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-file-earmark-check fs-4 text-primary"></i>
      <div>
        <h4 class="mb-0">{{ __('My Tests') }}</h4>
        <p class="text-muted mb-0 small">{{ __('Create and manage your sociometric tests.') }}</p>
      </div>
    </div>
  </div>

  <livewire:test-manager />
</div>
@endsection
