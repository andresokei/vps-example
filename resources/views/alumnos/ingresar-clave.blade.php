@extends('layouts.custom')

@section('content')
<section class="public-flow">
    <div class="public-shell public-shell--narrow">
        <div class="public-card">
            <div class="public-card__header">
                <span class="public-card__eyebrow">
                    <i class="fas fa-key"></i>
                    Acceso del alumno
                </span>
                <h1 class="public-card__title">Ingresar clave de acceso</h1>
                <p class="public-card__subtitle">Introduce la clave entregada por tu profesor para abrir el test del grupo.</p>
            </div>

            <div class="public-card__body public-card__body--compact">
                <div class="public-stack">
                    @if ($errors->has('clave_acceso'))
                        <div class="public-alert public-alert--danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ $errors->first('clave_acceso') }}</span>
                        </div>
                    @elseif (session('error'))
                        <div class="public-alert public-alert--danger">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <form action="{{ route('test.verificar') }}" method="POST" class="public-stack">
                        @csrf
                        <div>
                            <label for="clave_acceso" class="public-label">Clave de acceso</label>
                            <input
                                type="text"
                                name="clave_acceso"
                                id="clave_acceso"
                                class="public-input"
                                value="{{ old('clave_acceso') }}"
                                placeholder="Ejemplo: CLASE123"
                                required
                                autofocus
                            >
                            <p class="public-help">La clave distingue cada aplicacion del test.</p>
                        </div>

                        <div class="public-actions">
                            <button type="submit" class="public-button">
                                <i class="fas fa-arrow-right"></i>
                                Verificar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
