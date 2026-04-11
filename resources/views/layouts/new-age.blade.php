<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>@yield('title', 'Sociogram')</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}" />

        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />

        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,600;1,600&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Mulish:ital,wght@0,300;0,500;0,600;0,700;1,300;1,500;1,600;1,700&amp;display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,400;1,400&amp;display=swap" rel="stylesheet" />

        <!-- Core theme CSS (includes Bootstrap) -->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />

        <style>
            /* Badge pill hero */
            .badge-pill-landing {
                display: inline-block;
                background: linear-gradient(45deg, rgba(41,55,240,0.1), rgba(159,26,226,0.1));
                color: #2937f0;
                border: 1px solid rgba(41,55,240,0.25);
                border-radius: 50rem;
                padding: 0.35rem 1rem;
                font-size: 0.8rem;
                font-weight: 700;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                margin-bottom: 1.25rem;
            }

            /* Stats strip */
            .stats-strip {
                background: #fff;
                border-top: 1px solid #e9ecef;
                border-bottom: 1px solid #e9ecef;
                padding: 3rem 0;
            }
            .stats-strip .stat-number {
                font-family: "Kanit", sans-serif;
                font-size: 2.5rem;
                font-weight: 700;
                background: linear-gradient(45deg, #2937f0, #9f1ae2);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                line-height: 1.1;
            }
            .stats-strip .stat-label {
                color: #6c757d;
                font-size: 0.9rem;
                margin-top: 0.25rem;
            }

            /* Feature cards */
            .feature-card {
                background: #fff;
                border: 1px solid #e9ecef;
                border-radius: 1rem;
                padding: 2rem 1.5rem;
                height: 100%;
                transition: box-shadow 0.2s ease, transform 0.2s ease;
            }
            .feature-card:hover {
                box-shadow: 0 0.75rem 2rem rgba(41,55,240,0.12);
                transform: translateY(-4px);
            }
            .feature-card .icon-wrap {
                width: 3.5rem;
                height: 3.5rem;
                border-radius: 0.75rem;
                background: linear-gradient(45deg, rgba(41,55,240,0.08), rgba(159,26,226,0.08));
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1.25rem;
            }
            .feature-card .icon-feature { font-size: 1.75rem; }

            /* Cómo funciona */
            .how-it-works { background-color: #f8f9fa; }
            .step-number {
                width: 3rem;
                height: 3rem;
                border-radius: 50%;
                background: linear-gradient(45deg, #2937f0, #9f1ae2);
                color: #fff;
                font-family: "Kanit", sans-serif;
                font-size: 1.25rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1rem;
            }

            /* CTA gradient */
            .cta-gradient {
                background: linear-gradient(135deg, #1a21c8 0%, #2937f0 40%, #9f1ae2 100%);
                padding: 6rem 0 !important;
            }

            #mainNav .navbar-toggler {
                border: 1px solid rgba(41, 55, 240, 0.15);
                border-radius: 999px;
            }

            #mainNav .navbar-collapse {
                margin-top: 1rem;
                padding-top: 1rem;
                border-top: 1px solid #e9ecef;
            }

            #mainNav .landing-nav-links {
                gap: 0.25rem;
            }

            #mainNav .landing-nav-actions {
                display: flex;
                flex-direction: column;
                gap: 0.75rem;
                width: 100%;
            }

            #mainNav .landing-lang-switcher {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            #mainNav .landing-lang-switcher form {
                margin: 0;
            }

            #mainNav .landing-lang-switcher .btn,
            #mainNav .landing-feedback-btn {
                width: 100%;
            }

            @media (min-width: 992px) {
                #mainNav .navbar-collapse {
                    margin-top: 0;
                    padding-top: 0;
                    border-top: 0;
                }

                #mainNav .landing-nav-actions {
                    flex-direction: row;
                    align-items: center;
                    justify-content: flex-end;
                    gap: 0.75rem;
                    width: auto;
                    margin-left: 1rem;
                }

                #mainNav .landing-lang-switcher .btn,
                #mainNav .landing-feedback-btn {
                    width: auto;
                }
            }
        </style>
    </head>
    <body id="page-top">

        <!-- Navigation -->
        @include('partials.navbar')

        <!-- Page Content -->
        @yield('content')

        <!-- Footer -->
        <footer class="bg-black text-center py-5">
            <div class="container px-5">
                <div class="text-white-50 small">
                    <div class="mb-1 fw-bold text-white" style="font-family:'Kanit',sans-serif;font-size:1.1rem;">Sociogram</div>
                    <div class="mb-2">&copy; Sociogram {{ date('Y') }}. {{ __('All rights reserved.') }}</div>
                    <a href="{{ route('login') }}">{{ __('Log in') }}</a>
                    <span class="mx-1">&middot;</span>
                    <a href="{{ route('register') }}">{{ __('Register') }}</a>
                </div>
            </div>
        </footer>

        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

        <!-- Core theme JS-->
        <script src="{{ asset('js/script.js') }}"></script>

    </body>
</html>
