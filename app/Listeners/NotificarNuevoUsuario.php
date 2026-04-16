<?php

namespace App\Listeners;

use App\Mail\NuevoUsuarioMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class NotificarNuevoUsuario
{
    public function handle(Registered $event): void
    {
        $adminEmail = config('app.admin_email');

        if ($adminEmail) {
            Mail::to($adminEmail)->send(new NuevoUsuarioMail($event->user));
        }
    }
}
