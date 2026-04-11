@extends('layouts.custom')

@section('content')
<section class="public-flow">
    <div class="public-shell public-shell--narrow">
        <div class="public-card">
            <div class="public-card__body public-card__body--compact">
                <div class="public-result">
                    <div class="public-result__icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h1 class="public-result__title">{{ __('Answers saved successfully') }}</h1>
                    <p class="public-result__text">{{ __('Thank you for completing the test. You can close this window or return to the panel.') }}</p>

                    <div class="public-actions">
                        <a href="{{ route('dashboard') }}" class="public-button">
                            <i class="fas fa-arrow-left"></i>
                            {{ __('Return to dashboard') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
