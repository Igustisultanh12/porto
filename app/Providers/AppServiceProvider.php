<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Inertia\Ssr\SsrRenderFailed;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        RateLimiter::for('login', function (Request $request): array {
            $email = $request->input('email');
            $normalized = is_string($email) ? mb_strtolower($email) : '';

            return [Limit::perMinute(5)->by($normalized.'|'.$request->ip()), Limit::perMinute(20)->by($request->ip())];
        });
        RateLimiter::for('contact', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        Event::listen(SsrRenderFailed::class, function (SsrRenderFailed $event): void {
            Log::warning('Inertia SSR render failed', ['component' => $event->component(), 'type' => $event->type->value]);
        });
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }
    }
}
