<?php

namespace App\Providers;

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $allowedIPs = array_map('trim', explode(',', config('app.debug_allowed_ips', '')));

        $allowedIPs = array_filter($allowedIPs);

        if (empty($allowedIPs)) {
            return;
        }

        if (in_array(Request::ip(), $allowedIPs)) {
            Debugbar::enable();
        } else {
            Debugbar::disable();
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });

        // Register React SPA routes before Bagisto's shop routes are loaded.
        // This ensures the SPA is served for all non-admin web routes.
        // Admin routes (/admin/*) are excluded so Bagisto's admin panel still works.
        Route::middleware('web')
            ->group(function () {
                Route::get('/{any}', function () {
                    $spa = public_path('spa.html');

                    return file_exists($spa)
                        ? response()->file($spa)
                        : response('Frontend not built.', 503);
                })->where('any', '^(?!admin|api|sanctum).*');
            });
    }
}
