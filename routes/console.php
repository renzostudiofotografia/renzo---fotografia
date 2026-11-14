<?php

use App\Models\User;
use App\Models\LandingPage;
use App\LandingContent;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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

Artisan::command('landing:wedding {slug=bodas} {--create : Crear la landing como borrador si no existe}', function (LandingContent $content): int {
    $slug = (string) $this->argument('slug');
    if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) !== 1 || strlen($slug) > 120) {
        $this->error('Usa un slug válido, con letras minúsculas, números y guiones.');

        return 1;
    }
    $landing = LandingPage::where('slug', $slug)->first();
    if (! $landing) {
        if (! $this->option('create')) {
            $this->error('No existe esa landing. Usa --create para crearla como borrador.');

            return 1;
        }
        if (LandingPage::withTrashed()->where('slug', $slug)->orWhereJsonContains('previous_slugs', $slug)->exists()) {
            $this->error('El enlace pertenece a una landing existente o a la papelera. Restáurala desde el administrador.');

            return 1;
        }
        $landing = LandingPage::create([
            'name' => 'Bodas · Renzo Núñez Melgar', 'slug' => $slug,
            'draft' => $content->validate($content->defaults('wedding')), 'revision' => 1,
        ]);
    } else {
        DB::transaction(function () use ($landing, $content): void {
            $current = LandingPage::whereKey($landing->id)->lockForUpdate()->firstOrFail();
            $directory = storage_path('app/landing-backups');
            File::ensureDirectoryExists($directory);
            $backup = $directory.'/'.$current->slug.'-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4)).'.json';
            File::put($backup, json_encode($current->getAttributes(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
            $current->update([
                'draft' => $content->validate($content->weddingDraft($current->draft)), 'revision' => $current->revision + 1,
            ]);
            $this->info('Copia anterior conservada en '.$backup);
        });
    }
    $this->info('Diseño preparado como borrador. Revisa el contenido antes de publicar.');
    $this->line(route('admin.landings.edit', $landing));

    return 0;
})->purpose('Preparar el diseño editorial de bodas sin modificar la versión publicada');
