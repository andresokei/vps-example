<?php

use App\Models\User;
use App\Mail\NuevoUsuarioMail;
use App\Notifications\QueuedVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Notifications\SendQueuedNotifications;

uses(Tests\TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));

    $user = User::where('email', 'test@example.com')->firstOrFail();

    expect($user->rol)->toBe('profesor')
        ->and($user->hasRole('profesor'))->toBeTrue();
});

test('registration sends one administrator notification', function () {
    config()->set('app.admin_email', 'admin@example.com');
    Mail::fake();
    Notification::fake();

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    Mail::assertSent(NuevoUsuarioMail::class, 1);
    Notification::assertSentTo(User::where('email', 'test@example.com')->firstOrFail(), QueuedVerifyEmail::class, 1);
});

test('registration queues one administrator notice and one verification email', function () {
    config()->set('app.admin_email', 'admin@example.com');
    Queue::fake();
    Mail::fake();

    $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    Queue::assertPushed(CallQueuedListener::class, 1);
    Queue::assertPushed(SendQueuedNotifications::class, 1);
    Mail::assertNothingSent();
});
