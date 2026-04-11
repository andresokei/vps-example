@extends('layouts.custom')

@section('content')
<section class="public-flow public-flow--wide">
    <div class="public-shell">
        <div class="public-card">
            <div class="public-card__header">
                <span class="public-card__eyebrow">
                    <i class="fas fa-clipboard-check"></i>
                    {{ __('Sociometric test') }}
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
                    data-max-selections="{{ $selectionCount }}"
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
                        <label for="estudiante_quien_responde" class="public-label">{{ __('Select the student who is answering') }}</label>
                        <select
                            name="estudiante_id"
                            id="estudiante_quien_responde"
                            class="public-select"
                            data-respondent-select
                            required
                        >
                            <option value="">{{ __('Select a student') }}</option>
                            @foreach($estudiantes as $estudiante)
                                <option value="{{ $estudiante->id }}" @selected(old('estudiante_id') == $estudiante->id)>
                                    {{ $estudiante->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <p class="public-help">
                            {{ $selectionCount === 1
                                ? __('Each question requires :count distinct selection.', ['count' => $selectionCount])
                                : __('Each question requires :count distinct selections.', ['count' => $selectionCount]) }}
                        </p>
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
                                        {{ $pregunta->tipo_pregunta === 'rechazo' ? __('Rejection (badge)') : __('Preference (badge)') }}
                                    </span>
                                </div>

                                <input type="hidden" name="tipo_relacion_{{ $pregunta->id }}" value="{{ $pregunta->tipo_pregunta }}">

                                <div class="test-form__selection-box">
                                    <div class="test-form__selection-meta">
                                        <span>{{ __('Selected') }}</span>
                                        <span><span class="selected-count">0</span>/{{ $selectionCount }}</span>
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

                                @for ($i = 1; $i <= $selectionCount; $i++)
                                    <input type="hidden" name="respuesta_{{ $pregunta->id }}_{{ $i }}" class="response-input" value="{{ old('respuesta_'.$pregunta->id.'_'.$i) }}">
                                @endfor

                                @error('respuesta_'.$pregunta->id.'_1')
                                    <div class="test-form__question-error">{{ $message }}</div>
                                @enderror
                            </section>
                        @endforeach
                    @else
                        <div class="public-alert public-alert--warning">
                            <i class="fas fa-triangle-exclamation"></i>
                            <span>{{ __('This test has no available questions.') }}</span>
                        </div>
                    @endif

                    <div class="public-actions">
                        <button type="submit" class="public-button" data-submit-button>
                            <i class="fas fa-paper-plane"></i>
                            {{ __('Submit answers') }}
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
