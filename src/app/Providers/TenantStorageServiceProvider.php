<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use App\Services\TenantStorageService;

class TenantStorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Override the public disk URL after the application has booted
        $this->app->booted(function () {
            $config = config('filesystems.disks.public');
            $config['url'] = TenantStorageService::getTenantBaseUrl() . '/storage';
            
            // Reconfigure the public disk with the tenant-specific URL
            Storage::set('public', Storage::createLocalDriver($config));
        });
    }
} 