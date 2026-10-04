<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PageController extends Controller
{
    public function home(): View
    {
        return view('pages.home', [
            'portfolioItems' => config('site.portfolio'),
            'services' => config('site.services'),
            'testimonials' => config('site.testimonials'),
        ]);
    }

    public function about(): View
    {
        return view('pages.about');
    }

    public function portfolio(): View
    {
        return view('pages.portfolio', [
            'portfolioItems' => config('site.portfolio'),
        ]);
    }

    public function services(): View
    {
        return view('pages.services.index', [
            'services' => config('site.services'),
        ]);
    }

    public function service(string $service): View
    {
        $serviceData = collect(config('site.services'))
            ->firstWhere('slug', $service);

        abort_unless($serviceData, 404);

        return view('pages.services.show', [
            'service' => $serviceData,
        ]);
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function submitContact(Request $request): RedirectResponse
    {
        $request->validate(
            [
                'couple_names' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:160'],
                'phone' => ['required', 'string', 'max:40'],
                'event_date' => ['required', 'date', 'after_or_equal:today'],
                'location' => ['required', 'string', 'max:160'],
                'service' => ['required', 'in:wedding,prewedding,elopement,destination'],
                'message' => ['required', 'string', 'max:2000'],
            ],
            [
                'required' => 'El campo :attribute es obligatorio.',
                'email' => 'Ingresa un correo electrónico válido.',
                'max' => 'El campo :attribute no puede superar :max caracteres.',
                'event_date.after_or_equal' => 'La fecha del evento debe ser hoy o una fecha futura.',
                'service.in' => 'Selecciona una experiencia válida.',
            ],
            [
                'couple_names' => 'nombres de la pareja',
                'phone' => 'teléfono',
                'event_date' => 'fecha del evento',
                'location' => 'lugar de la celebración',
                'service' => 'tipo de experiencia',
                'message' => 'mensaje',
            ]
        );

        return back()->with(
            'success',
            'Gracias por compartir su historia. La consulta fue recibida y será atendida en menos de 24 horas.'
        );
    }
}
