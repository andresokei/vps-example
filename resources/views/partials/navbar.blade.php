<nav class="navbar navbar-expand-lg navbar-light fixed-top shadow-sm" id="mainNav">
    <div class="container px-5">
        <a class="navbar-brand fw-bold" href="#page-top">Sociogram</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            Menu
            <i class="bi-list"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav ms-auto my-3 my-lg-0 landing-nav-links">
                @guest
                    <li class="nav-item"><a class="nav-link me-lg-3" href="#features">{{ __('Features') }}</a></li>
                    <li class="nav-item"><a class="nav-link me-lg-3" href="#como-funciona">{{ __('How it works') }}</a></li>
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('login') }}">{{ __('Log in') }}</a></li>
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ route('register') }}">{{ __('Register') }}</a></li>
                @endguest

                @auth
                    <li class="nav-item"><a class="nav-link me-lg-3" href="{{ url('/dashboard') }}">{{ __('Profile') }}</a></li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link btn btn-link" style="color: inherit; text-decoration: none;">{{ __('Log out') }}</button>
                        </form>
                    </li>
                @endauth
            </ul>

            <div class="landing-nav-actions">
                {{-- Language Switcher --}}
                <div class="landing-lang-switcher">
                    @foreach(['es' => 'ES', 'en' => 'EN'] as $lang => $label)
                        <form action="{{ route('locale.switch') }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $lang }}">
                            <button type="submit"
                                class="lang-text-btn {{ app()->getLocale() === $lang ? 'lang-text-btn--active' : 'lang-text-btn--inactive' }}">
                                {{ $label }}
                            </button>
                        </form>
                        @if(!$loop->last)<span class="lang-sep">|</span>@endif
                    @endforeach
                </div>

                <button class="btn btn-primary rounded-pill px-3 landing-feedback-btn" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                    <span class="d-flex align-items-center justify-content-center">
                        <i class="bi-chat-text-fill me-2"></i>
                        <span class="small">Feedback</span>
                    </span>
                </button>
            </div>
        </div>
    </div>
</nav>
