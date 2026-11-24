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

  <details class="mb-3" @if($errors->has('plantilla')) open @endif>
    <summary class="btn btn-outline-primary btn-sm"><i class="bi bi-upload me-1"></i>{{ __('plantillas.importar') }}</summary>
    <form method="POST" action="{{ route('tests.plantilla.importar') }}" enctype="multipart/form-data" class="mt-3">
      @csrf
      <label for="archivo-plantilla" class="form-label">{{ __('plantillas.archivo') }}</label>
      <div class="d-flex flex-wrap gap-2 align-items-start">
        <input type="file" id="archivo-plantilla" name="plantilla" required accept=".json,application/json"
               class="form-control form-control-sm w-auto" aria-describedby="ayuda-plantilla">
        <button type="submit" class="btn btn-primary btn-sm">{{ __('plantillas.importar') }}</button>
      </div>
      <div id="ayuda-plantilla" class="form-text">{{ __('plantillas.ayuda') }}</div>
      @error('plantilla') <div class="text-danger small" role="alert">{{ $message }}</div> @enderror
    </form>
  </details>
  <livewire:test-manager />
</div>
@endsection
