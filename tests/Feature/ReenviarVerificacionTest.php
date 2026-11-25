<?php

use App\Mail\RecuperacionVerificacionMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    Mail::fake();
    $this->log = tempnam(sys_get_temp_dir(), 'verif');
    unlink($this->log);
    $this->travelTo(now('Europe/Madrid')->setTime(11, 0));
});

afterEach(function () {
    if (is_file($this->log)) {
        unlink($this->log);
    }
});

function usuarioPendiente(string $creado = '2026-09-10 12:00:00', array $extra = []): User
{
    return User::factory()->create(array_merge([
        'email_verified_at' => null,
        'created_at' => $creado,
    ], $extra));
}

it('simula por defecto y no envía ni escribe registro', function () {
    usuarioPendiente();

    $this->artisan('verificacion:reenviar', ['--log' => $this->log])->assertSuccessful();

    Mail::assertNothingSent();
    expect(is_file($this->log))->toBeFalse();
});

it('envía solo con --send, respeta el límite y empieza por las altas más recientes', function () {
    $viejo = usuarioPendiente('2026-08-01 10:00:00');
    $nuevo = usuarioPendiente('2026-09-25 10:00:00');

    $this->artisan('verificacion:reenviar', ['--send' => true, '--limit' => 1, '--log' => $this->log])->assertSuccessful();

    Mail::assertSent(RecuperacionVerificacionMail::class, 1);
    Mail::assertSent(RecuperacionVerificacionMail::class, fn ($m) => $m->hasTo($nuevo->email));
    Mail::assertNotSent(RecuperacionVerificacionMail::class, fn ($m) => $m->hasTo($viejo->email));
});

it('no reenvía a cuentas ya registradas, verificadas, fuera de ventana o desechables', function () {
    $ya = usuarioPendiente();
    file_put_contents($this->log, json_encode(['id' => $ya->id, 'status' => 'attempted', 'at' => '2026-09-30T00:04:03+00:00']).PHP_EOL);
    usuarioPendiente('2026-09-12 10:00:00', ['email_verified_at' => now()]);
    usuarioPendiente('2026-10-15 10:00:00');
    usuarioPendiente('2026-09-12 10:00:00', ['email' => 'x@mailinator.com']);
    $bueno = usuarioPendiente('2026-09-12 10:00:00');

    $this->artisan('verificacion:reenviar', ['--send' => true, '--log' => $this->log])->assertSuccessful();

    Mail::assertSent(RecuperacionVerificacionMail::class, 1);
    Mail::assertSent(RecuperacionVerificacionMail::class, fn ($m) => $m->hasTo($bueno->email));
});

it('aplica el tope diario contando el registro y registra antes de enviar', function () {
    usuarioPendiente();
    usuarioPendiente();
    file_put_contents($this->log, json_encode(['id' => 99999, 'status' => 'attempted', 'at' => now('UTC')->toIso8601String()]).PHP_EOL);

    $this->artisan('verificacion:reenviar', ['--send' => true, '--max-per-day' => 2, '--log' => $this->log])->assertSuccessful();

    Mail::assertSent(RecuperacionVerificacionMail::class, 1);
    expect(substr_count(file_get_contents($this->log), '"attempted"'))->toBe(2);
});

it('se niega a enviar fuera de horario salvo --ignore-hours', function () {
    usuarioPendiente();
    $this->travelTo(now('Europe/Madrid')->setTime(2, 0));

    $this->artisan('verificacion:reenviar', ['--send' => true, '--log' => $this->log])->assertFailed();
    Mail::assertNothingSent();

    $this->artisan('verificacion:reenviar', ['--send' => true, '--ignore-hours' => true, '--log' => $this->log])->assertSuccessful();
    Mail::assertSent(RecuperacionVerificacionMail::class, 1);
});

it('genera un enlace firmado que verifica la cuenta y la validez configurada', function () {
    $user = usuarioPendiente();

    $html = (new RecuperacionVerificacionMail($user, 48))->render();

    expect($html)->toContain('verify-email/'.$user->id.'/'.sha1($user->email))
        ->toContain('48 horas')
        ->toContain('forgot-password');
});

it('el enlace del correo verifica la cuenta al abrirlo con sesión iniciada', function () {
    $user = usuarioPendiente('2026-09-10 12:00:00');
    $html = (new RecuperacionVerificacionMail($user, 48))->render();
    preg_match('/href="([^"]*verify-email[^"]*)"/', $html, $m);
    $url = html_entity_decode($m[1]);

    $this->actingAs($user)->get($url)->assertRedirect();

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();
});

it('vigilar avisa solo cuando hay altas y ninguna verificación', function () {
    config(['app.admin_email' => 'admin@example.test']);
    User::factory()->count(6)->create(['email_verified_at' => null, 'created_at' => now()->subHours(2)]);

    $this->artisan('verificacion:vigilar')->assertFailed();

    User::query()->first()->forceFill(['email_verified_at' => now()])->save();
    $this->artisan('verificacion:vigilar', ['--no-mail' => true])->assertSuccessful();
});
