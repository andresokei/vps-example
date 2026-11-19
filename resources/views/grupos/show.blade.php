@extends('layouts.dashboard')
@section('title', $grupo->nombre_grupo)

@section('content')
  <livewire:group-roster :grupo="$grupo" />
@endsection
