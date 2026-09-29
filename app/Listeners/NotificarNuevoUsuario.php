<?php

namespace App\Listeners;

use App\Mail\NuevoUsuarioMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class NotificarNuevoUsuario implements ShouldQueue
{
    public int $tries = 3;

    public function viaQueue(): string
    {
        return 'mail';
    }

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(Registered $event): void
    {
        $adminEmail = config('app.admin_email');

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NuevoUsuarioMail($event->user));
        }
    }
}
