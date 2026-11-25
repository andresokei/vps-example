<x-guest-layout>
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-800" style="font-family: 'Kanit', sans-serif;">{{ __('landing.create') }}</h2>
        <p class="text-sm text-gray-500 mt-1">{{ __('landing.free') }}</p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf
        {{-- Honeypot: campo trampa para bots --}}
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;">

        <!-- Nombre -->
        <div>
            <x-input-label for="name" :value="__('landing.name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Correo electrónico -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('landing.email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Contraseña -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('landing.password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirmar contraseña -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('landing.confirm')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center py-3">
                {{ __('landing.register') }}
            </x-primary-button>
        </div>

        <p class="mt-4 text-center text-sm text-gray-500">
            {{ __('landing.existing') }}
            <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                {{ __('landing.signin') }}
            </a>
        </p>
    </form>
</x-guest-layout>
