@extends('layouts.custom')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="col-lg-6 col-md-8">
        <div class="card shadow-sm" style="border-radius: 15px; border: none;">
            <div class="card-body p-5">   
                <h2 class="mb-4 text-center" style="font-weight: bold; color: #4A73F1;">{{ $test->nombre_test }}</h2>

                <form action="{{ route('test.submit', ['id' => $test->id]) }}" method="POST">
                    @csrf
                
                    <!-- Campo oculto para pasar el asignacion_id -->
                    <input type="hidden" name="asignacion_id" value="{{ $asignacion_id }}">

                    <!-- Selección de estudiante global -->
                    <div class="form-group mb-4">
                        <label for="estudiante" class="font-weight-bold">Selecciona el Estudiante:</label>
                        <select name="estudiante_id" class="form-control" required>
                            <option value="">-- Selecciona un estudiante --</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}">{{ $estudiante->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Preguntas del test con dropdowns para seleccionar estudiantes en cada pregunta -->
                    @foreach ($test->preguntas as $pregunta)
                    <div class="form-group mb-4">
                        <label style="font-size: 1.2em; color: #555; display: block;">{{ $pregunta->texto_pregunta }}</label>
                        
                        <!-- Campo oculto para el tipo de relación (preferencia o rechazo) -->
                        <input type="hidden" name="tipo_relacion_{{ $pregunta->id }}" value="{{ $pregunta->tipo_pregunta }}">
                
                        <!-- Dropdown para la primera preferencia -->
                        <label>Primera Preferencia:</label>
                        <select name="respuesta_{{ $pregunta->id }}_1" class="form-control" required>
                            <option value="">-- Selecciona un estudiante --</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}">{{ $estudiante->nombre }}</option>
                            @endforeach
                        </select>
                
                        <!-- Dropdown para la segunda preferencia -->
                        <label>Segunda Preferencia:</label>
                        <select name="respuesta_{{ $pregunta->id }}_2" class="form-control" required>
                            <option value="">-- Selecciona un estudiante --</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}">{{ $estudiante->nombre }}</option>
                            @endforeach
                        </select>
                
                        <!-- Dropdown para la tercera preferencia -->
                        <label>Tercera Preferencia:</label>
                        <select name="respuesta_{{ $pregunta->id }}_3" class="form-control" required>
                            <option value="">-- Selecciona un estudiante --</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}">{{ $estudiante->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
                


                    <button type="submit" class="btn btn-primary">Enviar Respuestas</button>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
