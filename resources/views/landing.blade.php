@extends('layouts.new-age')

@section('title', 'Sociogram – ' . __('Understand your class like never before'))

@section('content')

<!-- Masthead / Hero -->
<header class="masthead">
    <div class="container px-5">
        <div class="row gx-5 align-items-center">
            <div class="col-lg-6">
                <div class="mb-5 mb-lg-0 text-center text-lg-start">
                    <span class="badge-pill-landing">{{ __('For teachers') }}</span>
                    <h1 class="display-1 lh-1 mb-3">
                        {{ __('Understand your class like never before') }}
                    </h1>
                    <p class="lead fw-normal text-muted mb-5">
                        {{ __('Sociogram turns sociometric tests into visual maps of relationships. Identify leaders, isolated students and group dynamics in minutes, directly from your browser.') }}
                    </p>
                    <div class="d-flex flex-column flex-lg-row align-items-center gap-3">
                        <a class="btn btn-primary rounded-pill px-4 py-2" href="{{ route('register') }}">
                            {{ __('Sign up free') }}
                        </a>
                        <a class="btn btn-link text-muted text-decoration-none" href="{{ route('login') }}">
                            {{ __('Already have an account? Log in →') }}
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="masthead-device-mockup">
                    <img src="{{ asset('assets/img/1.png') }}" alt="Vista previa de Sociogram" style="max-width: 100%;">
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Features -->
<section id="features">
    <div class="container px-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">{{ __('Everything you need to understand your classroom') }}</h2>
            <p class="text-muted lead">{{ __('Tools designed specifically for the educational context.') }}</p>
        </div>
        <div class="row gx-4 gy-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-person-lines-fill icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">{{ __('Visualize Sociograms') }}</h5>
                    <p class="text-muted mb-0 small">
                        {{ __('Automatic graphical representations of student relationships.') }}
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-bar-chart icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">{{ __('Detailed Analysis') }}</h5>
                    <p class="text-muted mb-0 small">
                        {{ __('Cohesion, reciprocity and centrality indices for each group.') }}
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-cloud-download icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">{{ __('Export Results') }}</h5>
                    <p class="text-muted mb-0 small">
                        {{ __('Download sociograms and analyses in PDF and CSV to share with your team.') }}
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-shield-lock icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">{{ __('Privacy Assured') }}</h5>
                    <p class="text-muted mb-0 small">
                        {{ __("Your students' data is stored securely and never shared.") }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Cómo funciona -->
<section class="how-it-works" id="como-funciona">
    <div class="container px-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">{{ __('How does it work?') }}</h2>
            <p class="text-muted lead">{{ __('In three steps your first sociogram is ready.') }}</p>
        </div>
        <div class="row gx-5 justify-content-center text-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="step-number">1</div>
                <h5 class="font-alt">{{ __('Create a test') }}</h5>
                <p class="text-muted small">
                    {{ __('Define the sociometric questions for your group.') }}
                </p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="step-number">2</div>
                <h5 class="font-alt">{{ __('Your students answer') }}</h5>
                <p class="text-muted small">
                    {{ __('Share a unique link. Students answer from any device, no registration or apps required.') }}
                </p>
            </div>
            <div class="col-md-4">
                <div class="step-number">3</div>
                <h5 class="font-alt">{{ __('Analyze the results') }}</h5>
                <p class="text-muted small">
                    {{ __('Sociogram generates the relationship graph instantly. Explore, filter and export from the panel.') }}
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-gradient" id="cta">
    <div class="container px-5 text-center">
        <h2 class="display-4 text-white fw-bold lh-1 mb-4">
            {{ __("Start understanding your classroom today. It's free.") }}
        </h2>
        <p class="text-white-50 lead mb-5">
            {{ __('No credit card. No installs. Ready in under 2 minutes.') }}
        </p>
        <a class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold"
           href="{{ route('register') }}"
           style="color:#2937f0;">
            {{ __('Create my free account') }}
        </a>
    </div>
</section>

@endsection
