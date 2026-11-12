<?php

namespace App\Providers;

use App\Models\User;
use App\SiteContent;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(SiteContent::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-site', fn (User $user): bool => (bool) $user->is_admin);
        Event::listen(RequestHandled::class, function (RequestHandled $event): void {
            if ($event->request->is('admin*') || $event->request->boolean('editor_preview')) {
                $event->response->headers->set('Cache-Control', 'private, no-store');
                $event->response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            }
        });
        View::composer(['pages.*', 'components.site.*', 'layouts.app'], function (\Illuminate\View\View $view): void {
            $view->with('site', app(SiteContent::class));
        });
    }
}
