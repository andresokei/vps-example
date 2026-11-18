@extends('layouts.dashboard')
@section('content')
<h1>Introducir o corregir respuestas</h1>
<p>{{ $asignacion->grupo->nombre_grupo }} · {{ $asignacion->test->localized_nombre_test }}</p>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<ul class="list-group">
@foreach($estudiantes as $estudiante)
<li class="list-group-item"><a href="{{ route('docente.editar', [$asignacion, $estudiante]) }}">{{ $estudiante->nombre }}</a></li>
@endforeach
</ul>
@endsection
