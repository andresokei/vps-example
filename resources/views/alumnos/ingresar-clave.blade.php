@extends('layouts.custom')

@section('content')
<section class="public-flow">
    <div class="public-shell public-shell--narrow">
        <div class="public-card">
            <div class="public-card__header">
                <span class="public-card__eyebrow">
                    <i class="fas fa-key"></i>
                    {{ __('Student access') }}
                </span>
                <h1 class="public-card__title">{{ __('Enter access key') }}</h1>
                <p class="public-card__subtitle">{{ __('Enter the key provided by your teacher to open the group test.') }}</p>
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
                            <label for="clave_acceso" class="public-label">{{ __('Access key') }}</label>
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
                            <p class="public-help">{{ __('The key identifies each test session.') }}</p>
                        </div>

                        <div class="public-actions">
                            <button type="submit" class="public-button">
                                <i class="fas fa-arrow-right"></i>
                                {{ __('Verify') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
