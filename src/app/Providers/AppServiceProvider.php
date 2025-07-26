<?php

namespace App\Providers;

use App\Events\EventCreated;
use App\Listeners\SendDiscordEventNotification;
use App\Services\TenantTeamResolver;
use Spatie\Permission\Contracts\PermissionsTeamResolver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the TenantTeamResolver for Spatie permissions
        $this->app->singleton(PermissionsTeamResolver::class, function ($app) {
            return new TenantTeamResolver();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register event listeners
        $this->app['events']->listen(
            EventCreated::class,
            SendDiscordEventNotification::class
        );
    }
}
