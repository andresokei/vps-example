@extends('layouts.new-age')

@section('title', 'Sociogram – Analiza las relaciones en tu aula')

@section('content')

<!-- Masthead / Hero -->
<header class="masthead">
    <div class="container px-5">
        <div class="row gx-5 align-items-center">
            <div class="col-lg-6">
                <div class="mb-5 mb-lg-0 text-center text-lg-start">
                    <span class="badge-pill-landing">Para docentes</span>
                    <h1 class="display-1 lh-1 mb-3">
                        Entiende a tu clase<br>
                        <span class="text-gradient">como nunca antes</span>
                    </h1>
                    <p class="lead fw-normal text-muted mb-5">
                        Sociogram convierte los tests sociométricos en mapas visuales de relaciones.
                        Identifica líderes, estudiantes aislados y dinámicas grupales en minutos,
                        directamente desde tu navegador.
                    </p>
                    <div class="d-flex flex-column flex-lg-row align-items-center gap-3">
                        <a class="btn btn-primary rounded-pill px-4 py-2" href="{{ route('register') }}">
                            Regístrate gratis
                        </a>
                        <a class="btn btn-link text-muted text-decoration-none" href="{{ route('login') }}">
                            ¿Ya tienes cuenta? Inicia sesión →
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
            <h2 class="fw-bold">Todo lo que necesitas para entender tu aula</h2>
            <p class="text-muted lead">Herramientas diseñadas específicamente para el contexto educativo.</p>
        </div>
        <div class="row gx-4 gy-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-person-lines-fill icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">Visualiza Sociogramas</h5>
                    <p class="text-muted mb-0 small">
                        Representaciones gráficas de las relaciones entre estudiantes generadas automáticamente.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-bar-chart icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">Análisis Detallados</h5>
                    <p class="text-muted mb-0 small">
                        Índices de cohesión, reciprocidad y centralidad para cada grupo.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-cloud-download icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">Exporta Resultados</h5>
                    <p class="text-muted mb-0 small">
                        Descarga los sociogramas y análisis en PDF y CSV para compartir con tu equipo.
                    </p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <i class="bi-shield-lock icon-feature text-gradient"></i>
                    </div>
                    <h5 class="font-alt mb-2">Privacidad Asegurada</h5>
                    <p class="text-muted mb-0 small">
                        Los datos de tus estudiantes se almacenan de forma segura y nunca se comparten.
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
            <h2 class="fw-bold">¿Cómo funciona?</h2>
            <p class="text-muted lead">En tres pasos tienes tu primer sociograma listo.</p>
        </div>
        <div class="row gx-5 justify-content-center text-center">
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="step-number">1</div>
                <h5 class="font-alt">Crea un test</h5>
                <p class="text-muted small">
                    Define las preguntas sociométricas para tu grupo — ¿con quién estudiarías? ¿a quién elegirías de compañero?
                </p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <div class="step-number">2</div>
                <h5 class="font-alt">Tus alumnos responden</h5>
                <p class="text-muted small">
                    Comparte un enlace único. Los estudiantes contestan desde cualquier dispositivo, sin registros ni apps.
                </p>
            </div>
            <div class="col-md-4">
                <div class="step-number">3</div>
                <h5 class="font-alt">Analiza los resultados</h5>
                <p class="text-muted small">
                    Sociogram genera el grafo de relaciones al instante. Explora, filtra y exporta desde el panel.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-gradient" id="cta">
    <div class="container px-5 text-center">
        <h2 class="display-4 text-white fw-bold lh-1 mb-4">
            Comienza a entender tu aula<br>
            <span style="opacity:0.85;">hoy mismo. Es gratis.</span>
        </h2>
        <p class="text-white-50 lead mb-5">
            Sin tarjeta de crédito. Sin instalaciones. Listo en menos de 2 minutos.
        </p>
        <a class="btn btn-light btn-lg rounded-pill px-5 py-3 fw-bold"
           href="{{ route('register') }}"
           style="color:#2937f0;">
            Crear mi cuenta gratis
        </a>
    </div>
</section>

@endsection
