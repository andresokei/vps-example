@extends('layouts.custom')

@section('content')
<div class="container text-center" style="min-height: 100vh;">
    <h1>¡Respuestas guardadas exitosamente!</h1>
    <p>Gracias por completar el test.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">Volver al Dashboard</a>
</div>
@endsection

