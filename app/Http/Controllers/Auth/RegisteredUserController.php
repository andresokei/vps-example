<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        if ($request->query('starter') === 'classroom') {
            $request->session()->put('url.intended', route('dashboard', ['starter' => 'classroom']));
        }

        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('website')) {
            abort(403);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'signup_landing_locale' => $request->session()->get('signup_landing_locale'),
        ]);

        $user->grantProfesorAccess();

        event(new Registered($user));

        Auth::login($user);

        if ($request->session()->get('url.intended') === route('dashboard', ['starter' => 'classroom'])) {
            return to_route('dashboard', ['starter' => 'classroom']);
        }

        return redirect(route('dashboard', absolute: false));
    }
}
