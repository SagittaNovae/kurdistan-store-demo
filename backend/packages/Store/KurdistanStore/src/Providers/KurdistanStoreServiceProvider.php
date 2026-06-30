<?php

namespace Store\KurdistanStore\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Store\KurdistanStore\Console\Commands\GenerateSitemap;
use Store\KurdistanStore\Console\Commands\SeedDeliveryZones;
use Store\KurdistanStore\Services\Phone\PhoneNormalizer;
use Store\KurdistanStore\Services\Phone\PhoneValidator;
use Store\KurdistanStore\Services\Seo\SeoService;
use Store\KurdistanStore\Services\Shipping\ShippingService;

class KurdistanStoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/kurdistan-store.php', 'kurdistan-store');

        $this->app->singleton(SeoService::class);
        $this->app->singleton(ShippingService::class);

        $this->app->singleton(PhoneNormalizer::class, fn () => new PhoneNormalizer(
            config('kurdistan-store.phone.default_country_code', '+964')
        ));

        $this->app->singleton(PhoneValidator::class);
    }

    public function boot(Router $router): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSitemap::class,
                SeedDeliveryZones::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/../../config/kurdistan-store.php' => config_path('kurdistan-store.php'),
        ], 'kurdistan-store-config');

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('kurdistan:generate-sitemap')->daily();
        });
    }
}
