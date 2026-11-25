<?php

use App\Models\Test;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('serves stable linked translations and counts a session once per language', function () {
    $this->get('/en?ref=school')->assertOk()->assertSee('rel="canonical" href="'.url('/en').'/"', false);
    $this->get('/en/?ref=school', ['Accept-Language' => 'es'])->assertOk()
        ->assertHeader('Content-Language', 'en')->assertSee('hreflang="es"', false)
        ->assertSee('How to create a classroom sociogram')->assertSee('Use this classroom template');
    $this->get('/en/?ref=school')->assertOk();
    $this->get('/', ['Accept-Language' => 'en'])->assertOk()->assertHeader('Content-Language', 'es')
        ->assertSee('hreflang="en"', false);
    expect((int) DB::table('landing_visits')->where('locale', 'en')->sum('sessions'))->toBe(1)
        ->and((int) DB::table('landing_visits')->where('locale', 'es')->sum('sessions'))->toBe(1);
});

it('carries the English classroom template through signup and email verification', function () {
    Event::fake([\Illuminate\Auth\Events\Registered::class]);
    $template = Test::create(['nombre_test' => 'Interacciones', 'nombre_test_en' => 'Classroom Interaction Analysis']);
    $this->get('/en/?ref=school')->assertOk();
    $this->withCookie('locale_preference', 'en')->get('/register?starter=classroom')->assertSee('Email address');
    $target = route('dashboard', ['starter' => 'classroom']);
    $this->post('/register', ['name' => 'Teacher', 'email' => 'teacher@example.org',
        'password' => 'demo-password', 'password_confirmation' => 'demo-password'])->assertRedirect($target);
    $this->get($target)->assertRedirect(route('verification.notice'));
    $user = User::where('email', 'teacher@example.org')->firstOrFail();
    expect($user->signup_landing_locale)->toBe('en');
    $signed = URL::temporarySignedRoute('verification.verify', now()->addHour(), ['id' => $user->id, 'hash' => sha1($user->email)]);
    $this->get($signed)->assertRedirect($target);
    $this->get($target)->assertOk()->assertSee('Classroom Interaction Analysis');
});
