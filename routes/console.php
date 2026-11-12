<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\URL;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('site:setup', function (): void {
    if (User::where('is_admin', true)->exists()) {
        $this->info('El propietario ya está configurado. Accede a '.url('/admin/login'));

        return;
    }
    $this->info('Abre este enlace en el navegador para elegir tu correo y contraseña. Vence en una hora:');
    $this->line(URL::temporarySignedRoute('admin.setup', now()->addHour()));
})->purpose('Crear un enlace privado para configurar al propietario del sitio');
