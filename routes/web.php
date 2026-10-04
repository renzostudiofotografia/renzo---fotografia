<?php

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
    Route::post('/contacto', 'submitContact')->name('contact.submit');
});
