<?php

namespace App\Helpers;

use App\Services\TenantStorageService;

if (!function_exists('tenant_storage_url')) {
    /**
     * Get a tenant-specific storage URL
     */
    function tenant_storage_url(string $path = ''): string
    {
        return TenantStorageService::getStorageUrl($path);
    }
}

if (!function_exists('tenant_asset_url')) {
    /**
     * Get a tenant-specific asset URL
     */
    function tenant_asset_url(string $path = ''): string
    {
        $baseUrl = TenantStorageService::getTenantBaseUrl();
        $assetPath = '/asset';
        
        if (!empty($path)) {
            $assetPath .= '/' . ltrim($path, '/');
        }
        
        return $baseUrl . $assetPath;
    }
}

if (!function_exists('tenant_url')) {
    /**
     * Get a tenant-specific URL
     */
    function tenant_url(string $path = ''): string
    {
        $baseUrl = TenantStorageService::getTenantBaseUrl();
        
        if (!empty($path)) {
            $path = '/' . ltrim($path, '/');
        }
        
        return $baseUrl . $path;
    }
} 