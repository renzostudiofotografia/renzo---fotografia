<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::controller(PageController::class)->group(function (): void {
    Route::get('/', 'home')->name('home');
    Route::get('/sobre-mi', 'about')->name('about');
    Route::get('/portafolio', 'portfolio')->name('portfolio');
    Route::get('/servicios', 'services')->name('services');
    Route::redirect('/servicios.html', '/servicios');
    Route::get('/servicios/{service}', 'service')
        ->where('service', 'cobertura-boda|preboda|postboda')
        ->name('services.show');
    Route::get('/contacto', 'contact')->name('contact');
    Route::get('/citas', 'booking')->name('booking');
    Route::post('/contacto', 'submitContact')->name('contact.submit');
});

Route::get('/media/{asset}', [MediaController::class, 'show'])->whereNumber('asset')->name('media.show');

Route::get('/lp/{slug}', [LandingPageController::class, 'show'])->name('landing.show');
Route::post('/lp/{slug}/leads', [LandingPageController::class, 'submitLead'])
    ->middleware('throttle:10,1')->name('landing.lead');

Route::prefix('admin/landings')->name('admin.landings.')->middleware(['auth', 'can:manage-site'])
    ->controller(LandingPageController::class)->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::get('/{landing}/edit', 'edit')->name('edit');
        Route::put('/{landing}', 'update')->name('update');
        Route::delete('/{landing}', 'destroy')->name('destroy');
        Route::post('/{landing}/restore', 'restore')->withTrashed()->name('restore');
        Route::match(['get', 'post'], '/{landing}/preview', 'preview')->name('preview');
        Route::post('/{landing}/publish', 'publish')->name('publish');
        Route::post('/{landing}/unpublish', 'unpublish')->name('unpublish');
        Route::post('/{landing}/video', [AdminController::class, 'uploadLandingVideo'])->middleware('throttle:20,1')->name('video');
        Route::get('/{landing}/leads', 'leads')->name('leads');
    });

Route::prefix('admin')->name('admin.')->controller(AdminController::class)->group(function (): void {
    Route::get('/login', 'login')->name('login');
    Route::post('/login', 'authenticate')->middleware('throttle:5,1')->name('authenticate');
    Route::match(['get', 'post'], '/setup', 'setup')->middleware(['signed', 'throttle:5,1'])->name('setup');
    Route::middleware(['auth', 'can:manage-site'])->group(function (): void {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::post('/logout', 'logout')->name('logout');
        Route::get('/editor/{section}', 'edit')->name('edit');
        Route::put('/editor/{section}', 'save')->name('save');
        Route::post('/editor/{section}/discard', 'discard')->name('discard');
        Route::post('/editor/{section}/reorder', 'reorder')->name('reorder');
        Route::post('/publish', 'publish')->name('publish');
        Route::get('/media', 'media')->name('media');
        Route::post('/media', 'upload')->middleware('throttle:20,1')->name('upload');
        Route::patch('/media/{asset}', 'updateMedia')->name('media.update');
        Route::post('/media/{asset}/cloudflare', 'sendToCloudflare')->middleware('throttle:10,1')->name('media.cloudflare');
        Route::post('/media/{asset}/r2', 'sendToR2')->middleware('throttle:10,1')->name('media.r2');
        Route::get('/integrations', 'integrations')->name('integrations');
    });
});
