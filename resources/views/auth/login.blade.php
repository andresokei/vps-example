<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800" style="font-family: 'Kanit', sans-serif;">{{ __('landing.welcome') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('landing.credentials') }}</p>
    </div>

    <!-- Estado de sesión -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Correo electrónico -->
        <div>
            <x-input-label for="email" :value="__('landing.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <div class="flex items-center justify-between">
                <x-input-label for="password" :value="__('landing.password')" />
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-xs text-indigo-600 hover:text-indigo-500">
                        {{ __('landing.forgot') }}
                    </a>
                @endif
            </div>
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- {{ __('landing.remember') }} -->
        <div class="mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('landing.remember') }}</span>
            </label>
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3">
                {{ __('landing.signin') }}
            </x-primary-button>
        </div>

        <p class="mt-4 text-center text-sm text-gray-500">
            {{ __('landing.no_account') }}
            <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                {{ __('landing.signup') }}
            </a>
        </p>
    </form>
</x-guest-layout>
