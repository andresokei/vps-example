@extends('layouts.dashboard')
@section('title', __('New Group'))

@section('content')
  <div class="page-header">
    <a class="d-inline-block mb-2" href="{{ route('grupos.index') }}">{{ __('My Groups') }}</a>
    <h1 class="page-title">{{ __('New Group') }}</h1>
  </div>
  <form method="post" action="{{ route('grupos.store') }}" class="card card-body gap-3" style="max-width:36rem">
    @csrf
    <div>
      <label for="nombre_grupo" class="form-label">{{ __('Group Name') }}</label>
      <input id="nombre_grupo" name="nombre_grupo" type="text" maxlength="255" required autofocus
             class="form-control @error('nombre_grupo') is-invalid @enderror" value="{{ old('nombre_grupo') }}"
             @error('nombre_grupo') aria-invalid="true" aria-describedby="group-name-error" @enderror>
      @error('nombre_grupo') <div id="group-name-error" class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div><button type="submit" class="btn btn-primary">{{ __('classroom.create') }}</button></div>
  </form>
@endsection
