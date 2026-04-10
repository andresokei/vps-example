@extends('layouts.custom')

@section('content')
<section class="public-flow public-flow--wide">
    <div class="public-shell">
        <div class="public-card">
            <div class="public-card__header">
                <span class="public-card__eyebrow">
                    <i class="fas fa-clipboard-check"></i>
                    Test sociometrico
                </span>
                <h1 class="public-card__title">{{ $test->nombre_test }}</h1>
                @if ($test->descripcion)
                    <p class="public-card__subtitle">{{ $test->descripcion }}</p>
                @endif
            </div>

            <div class="public-card__body">
                <form
                    id="testForm"
                    class="test-form"
                    action="{{ route('test.submit', $asignacion) }}"
                    method="POST"
                    data-test-form
                    data-max-selections="3"
                >
                    @csrf

                    @if ($errors->has('estudiante_id') || $errors->has('clave_acceso'))
                        <div class="public-alert public-alert--danger">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>{{ $errors->first('estudiante_id') ?: $errors->first('clave_acceso') }}</span>
                        </div>
                    @endif

                    <div id="validationMessage" class="public-alert public-alert--danger" data-validation-message hidden></div>

                    <div>
                        <label for="estudiante_quien_responde" class="public-label">Selecciona el estudiante que responde</label>
                        <select
                            name="estudiante_id"
                            id="estudiante_quien_responde"
                            class="public-select"
                            data-respondent-select
                            required
                        >
                            <option value="">Selecciona un estudiante</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}" @selected(old('estudiante_id') == $estudiante->id)>
                                    {{ $estudiante->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <p class="public-help">Cada pregunta requiere 3 selecciones distintas.</p>
                    </div>

                    @if($test && $test->preguntas && $test->preguntas->count())
                        @foreach ($test->preguntas as $pregunta)
                            <section
                                class="test-form__question @error('respuesta_'.$pregunta->id.'_1') is-invalid @enderror"
                                data-question-id="{{ $pregunta->id }}"
                                data-question-type="{{ $pregunta->tipo_pregunta }}"
                            >
                                <div class="test-form__question-header">
                                    <h2 class="test-form__question-title">{{ $pregunta->texto_pregunta }}</h2>
                                    <span class="test-form__question-badge test-form__question-badge--{{ $pregunta->tipo_pregunta === 'rechazo' ? 'rechazo' : 'preferencia' }}">
                                        {{ $pregunta->tipo_pregunta === 'rechazo' ? 'Rechazo' : 'Preferencia' }}
                                    </span>
                                </div>

                                <input type="hidden" name="tipo_relacion_{{ $pregunta->id }}" value="{{ $pregunta->tipo_pregunta }}">

                                <div class="test-form__selection-box">
                                    <div class="test-form__selection-meta">
                                        <span>Seleccionados</span>
                                        <span><span class="selected-count">0</span>/3</span>
                                    </div>
                                    <div class="test-form__selection-list selected-students-list"></div>
                                </div>

                                <div class="test-form__options">
                                    @foreach($estudiantes as $estudiante)
                                        <button
                                            type="button"
                                            class="student-choice"
                                            data-student-choice
                                            data-student-id="{{ $estudiante->id }}"
                                            data-student-name="{{ $estudiante->nombre }}"
                                        >
                                            <i class="student-choice__icon fas fa-user-circle"></i>
                                            <span class="student-choice__name">{{ $estudiante->nombre }}</span>
                                            <i class="student-choice__indicator fas fa-check-circle"></i>
                                        </button>
                                    @endforeach
                                </div>

                                <input type="hidden" name="respuesta_{{ $pregunta->id }}_1" class="response-input" value="{{ old('respuesta_'.$pregunta->id.'_1') }}">
                                <input type="hidden" name="respuesta_{{ $pregunta->id }}_2" class="response-input" value="{{ old('respuesta_'.$pregunta->id.'_2') }}">
                                <input type="hidden" name="respuesta_{{ $pregunta->id }}_3" class="response-input" value="{{ old('respuesta_'.$pregunta->id.'_3') }}">

                                @error('respuesta_'.$pregunta->id.'_1')
                                    <div class="test-form__question-error">{{ $message }}</div>
                                @enderror
                            </section>
                        @endforeach
                    @else
                        <div class="public-alert public-alert--warning">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span>No hay preguntas disponibles para este test.</span>
                        </div>
                    @endif

                    <div class="public-actions">
                        <button type="submit" class="public-button" data-submit-button>
                            <i class="fas fa-paper-plane"></i>
                            Enviar respuestas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="{{ asset('js/test-form.js') }}"></script>
@endpush
