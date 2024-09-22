@extends('layouts.new-age')

@section('title', 'Sociogram App - Landing Page')

@section('content')

<!-- Masthead section (Cabecera) -->
<header class="masthead">
    <div class="container px-5">
        <div class="row gx-5 align-items-center">
            <div class="col-lg-6">
                <div class="mb-5 mb-lg-0 text-center text-lg-start">
                    <h1 class="display-1 lh-1 mb-3">Analiza las relaciones en tu aula fácilmente</h1>
                    <p class="lead fw-normal text-muted mb-5">Con Sociogram.app puedes visualizar y comprender las dinámicas sociales de tu grupo de estudiantes en tiempo real. Todo desde tu navegador web.</p>
                    <div class="d-flex flex-column flex-lg-row align-items-center">
                        <a class="btn btn-primary rounded-pill" href="#signup">Regístrate Gratis</a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="masthead-device-mockup">
                    <img src="{{ asset('assets/img/1.png') }}" alt="Sociogram App Demo" style="max-width: 100%;">
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Testimonial Section -->
<aside class="text-center bg-gradient-primary-to-secondary">
    <div class="container px-5">
        <div class="row gx-5 justify-content-center">
            <div class="col-xl-8">
                <div class="h2 fs-1 text-white mb-4">"Una herramienta innovadora para comprender las dinámicas sociales en el aula."</div>
            </div>
        </div>
    </div>
</aside>

<!-- App features section (Características) -->
<section id="features">
    <div class="container px-5">
        <div class="row gx-5 align-items-center">
            <div class="col-lg-8 order-lg-1 mb-5 mb-lg-0">
                <div class="container-fluid px-5">
                    <div class="row gx-5">
                        <div class="col-md-6 mb-5">
                            <div class="text-center">
                                <i class="bi-person-lines-fill icon-feature text-gradient d-block mb-3"></i>
                                <h3 class="font-alt">Visualiza Sociogramas</h3>
                                <p class="text-muted mb-0">Crea representaciones gráficas de las relaciones entre tus estudiantes con solo unos clics.</p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-5">
                            <div class="text-center">
                                <i class="bi-bar-chart icon-feature text-gradient d-block mb-3"></i>
                                <h3 class="font-alt">Análisis Detallados</h3>
                                <p class="text-muted mb-0">Obtén información profunda sobre las dinámicas sociales y comportamientos grupales.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-5 mb-md-0">
                            <div class="text-center">
                                <i class="bi-cloud-download icon-feature text-gradient d-block mb-3"></i>
                                <h3 class="font-alt">Exporta los Resultados</h3>
                                <p class="text-muted mb-0">Descarga los sociogramas y análisis en formatos PDF y CSV que puedes compartir fácilmente.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <i class="bi-shield-lock icon-feature text-gradient d-block mb-3"></i>
                                <h3 class="font-alt">Privacidad Asegurada</h3>
                                <p class="text-muted mb-0">Protege los datos de tus estudiantes con nuestra tecnología de seguridad avanzada.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 order-lg-0">
                <!-- Imagen o gráfico que represente el análisis de sociogramas -->
                <img src="{{ asset('assets/img/5.png') }}" alt="Sociogram App Demo" style="max-width: 100%;">
            </div>
        </div>
    </div>
</section>

<!-- Call to Action Section -->
<section class="cta">
    <div class="cta-content">
        <div class="container px-5">
            <h2 class="text-white display-1 lh-1 mb-4">
                Comienza a usar Sociogram.app hoy mismo.
                <br />
                ¡Regístrate Gratis!
            </h2>
            <a class="btn btn-outline-light py-3 px-4 rounded-pill" href="#signup">Empieza Ahora</a>
        </div>
    </div>
</section>

@endsection
