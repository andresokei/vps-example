@extends('layouts.dashboard')

@section('title', 'Analisis')

@section('content')
  @livewire('analisis-selector', [
    'grupoInicial' => request()->integer('grupo') ?: null,
    'asignacionInicial' => request()->integer('asignacion') ?: null,
  ])
@endsection
