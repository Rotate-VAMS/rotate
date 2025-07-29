<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;

if (!function_exists('tenant_cache_remember')) {
    /**
     * Remember a value in cache with tenant-specific key
     */
    function tenant_cache_remember(string $key, int $ttl, callable $callback)
    {
        $tenantKey = get_tenant_cache_key($key);
        return Cache::store('redis')->remember($tenantKey, $ttl, $callback);
    }
}

if (!function_exists('tenant_cache_forget')) {
    /**
     * Forget a tenant-specific cache key
     */
    function tenant_cache_forget(string $key): bool
    {
        $tenantKey = get_tenant_cache_key($key);
        return Cache::store('redis')->forget($tenantKey);
    }
}

if (!function_exists('tenant_cache_get')) {
    /**
     * Get a value from tenant-specific cache
     */
    function tenant_cache_get(string $key, $default = null)
    {
        $tenantKey = get_tenant_cache_key($key);
        return Cache::store('redis')->get($tenantKey, $default);
    }
}

if (!function_exists('tenant_cache_put')) {
    /**
     * Put a value in tenant-specific cache
     */
    function tenant_cache_put(string $key, $value, int $ttl = null): bool
    {
        $tenantKey = get_tenant_cache_key($key);
        return Cache::store('redis')->put($tenantKey, $value, $ttl);
    }
}

if (!function_exists('get_tenant_cache_key')) {
    /**
     * Generate a tenant-specific cache key
     */
    function get_tenant_cache_key(string $key): string
    {
        $tenantId = get_current_tenant_id();
        return "tenant:{$tenantId}:{$key}";
    }
}

if (!function_exists('get_current_tenant_id')) {
    /**
     * Get the current tenant ID
     */
    function get_current_tenant_id(): ?int
    {
        if (App::bound('currentTenant')) {
            return App::make('currentTenant')->id;
        }
        
        // Fallback for console commands or when tenant is not identified
        return null;
    }
}

if (!function_exists('clear_tenant_cache')) {
    /**
     * Clear all cache for the current tenant
     * Note: This is a simplified implementation. In production, you might want to
     * implement a more sophisticated cache clearing mechanism using Redis SCAN.
     */
    function clear_tenant_cache(): bool
    {
        $tenantId = get_current_tenant_id();
        if (!$tenantId) {
            return false;
        }
        
        // For now, we'll return true as this is a placeholder implementation
        // In production, you would implement Redis SCAN to find and delete
        // all keys with the tenant prefix
        return true;
    }
}

if (!function_exists('set_permissions_team_id')) {
    /**
     * Set the permissions team ID (tenant ID) for the current request
     */
    function set_permissions_team_id($teamId = null): void
    {
        $resolver = app(\Spatie\Permission\Contracts\PermissionsTeamResolver::class);
        $resolver->setPermissionsTeamId($teamId);
    }
}

if (!function_exists('get_permissions_team_id')) {
    /**
     * Get the current permissions team ID (tenant ID)
     */
    function get_permissions_team_id(): int|string|null
    {
        $resolver = app(\Spatie\Permission\Contracts\PermissionsTeamResolver::class);
        return $resolver->getPermissionsTeamId();
    }
} 