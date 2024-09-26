@extends('layouts.custom')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm" style="border-radius: 15px; border: none;">
            <div class="card-body p-5">
                <h2 class="mb-4 text-center" style="font-weight: bold; color: #4A73F1;">{{ $test->nombre_test }}</h2>

                <form action="{{ route('test.submit', ['id' => $test->id]) }}" method="POST">
                    @csrf
                    @foreach ($test->preguntas as $pregunta)
                        <div class="form-group mb-4">
                            <!-- Pregunta en una línea -->
                            <label style="font-size: 1.2em; color: #555; display: block;">{{ $pregunta->texto_pregunta }}</label>
                            <!-- Input en la siguiente línea -->
                            @switch($pregunta->tipo_pregunta)
                                @case('texto')
                                    <input type="text" name="respuesta_{{ $pregunta->id }}" class="form-control" required style="border-radius: 10px; border: 1px solid #ddd; padding: 12px; margin-top: 8px;">
                                    @break

                                @case('opcion_multiple')
                                    @foreach ($pregunta->opciones as $opcion)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="respuesta_{{ $pregunta->id }}" value="{{ $opcion }}" id="opcion_{{ $opcion }}">
                                            <label class="form-check-label" for="opcion_{{ $opcion }}">
                                                {{ $opcion }}
                                            </label>
                                        </div>
                                    @endforeach
                                    @break

                                @case('multiple_choice')
                                    @foreach ($pregunta->opciones as $opcion)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="respuesta_{{ $pregunta->id }}[]" value="{{ $opcion }}" id="opcion_{{ $opcion }}">
                                            <label class="form-check-label" for="opcion_{{ $opcion }}">
                                                {{ $opcion }}
                                            </label>
                                        </div>
                                    @endforeach
                                    @break

                                @default
                                    <input type="text" name="respuesta_{{ $pregunta->id }}" class="form-control" required style="border-radius: 10px; border: 1px solid #ddd; padding: 12px; margin-top: 8px;">
                            @endswitch
                        </div>
                    @endforeach

                    <button type="submit" class="btn btn-primary">Enviar Respuestas</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
