<?php

namespace App\Services;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Request;

class TenantStorageService
{
    /**
     * Get the base URL for the current tenant
     */
    public static function getTenantBaseUrl(): string
    {
        // Get the current tenant from the application container
        if (App::bound('currentTenant')) {
            $tenant = App::make('currentTenant');
            
            // If we're in a console command, use the tenant's domain with a default scheme
            if (App::runningInConsole()) {
                return 'http://' . $tenant->domain;
            }

            return $tenant->getBaseUrl();
        }

        // If we're in a console command or no request context, fall back to APP_URL
        if (App::runningInConsole() || !Request::has('host')) {
            return config('app.url');
        }

        // Fallback to the current request's URL
        return Request::root();
    }

    /**
     * Get the storage URL for the current tenant
     */
    public static function getStorageUrl(string $path = ''): string
    {
        $baseUrl = self::getTenantBaseUrl();
        $storagePath = '/storage';
        
        if (!empty($path)) {
            $storagePath .= '/' . ltrim($path, '/');
        }
        
        return $baseUrl . $storagePath;
    }

    /**
     * Get the public disk URL for the current tenant
     */
    public static function getPublicDiskUrl(string $path = ''): string
    {
        return self::getStorageUrl($path);
    }
} 