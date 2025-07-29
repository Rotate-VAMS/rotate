# Tenant-Aware Caching Guide

This guide explains the tenant-aware caching system implemented in your multi-tenant Laravel application.

## Overview

The tenant-aware caching system ensures that cache keys are automatically prefixed with the current tenant ID, preventing data leakage between tenants and ensuring proper data isolation.

## Problem Solved

**Before**: All controllers used global cache keys like:
- `integration:logo`
- `pireps:list:all`
- `users:pilots:all`

This meant that data from different tenants could potentially be shared or overwritten.

**After**: All cache keys are automatically prefixed with tenant ID:
- `tenant:1:integration:logo`
- `tenant:1:pireps:list:all`
- `tenant:2:users:pilots:all`

## Implementation

### Helper Functions

The system provides several helper functions in `src/app/Helpers/TenantCacheHelper.php`:

```php
// Remember a value in cache with tenant-specific key
tenant_cache_remember(string $key, int $ttl, callable $callback)

// Forget a tenant-specific cache key
tenant_cache_forget(string $key): bool

// Get a value from tenant-specific cache
tenant_cache_get(string $key, $default = null)

// Put a value in tenant-specific cache
tenant_cache_put(string $key, $value, int $ttl = null): bool

// Generate a tenant-specific cache key
get_tenant_cache_key(string $key): string

// Get the current tenant ID
get_current_tenant_id(): ?int

// Clear all cache for the current tenant
clear_tenant_cache(): bool
```

### How It Works

1. **Tenant Identification**: The `IdentifyTenant` middleware identifies the current tenant based on the request domain
2. **Cache Key Generation**: The `get_tenant_cache_key()` function automatically prefixes cache keys with the tenant ID
3. **Automatic Integration**: All cache operations use tenant-specific keys without manual intervention

## Usage Examples

### In Controllers

**Before**:
```php
$data = Cache::store('redis')->remember('integration:logo', 1800, function () {
    return Documents::fetchDocument(Documents::DOCUMENT_TYPE_LOGO, 1);
});

Cache::store('redis')->forget('integration:logo');
```

**After**:
```php
$data = tenant_cache_remember('integration:logo', 1800, function () {
    return Documents::fetchDocument(Documents::DOCUMENT_TYPE_LOGO, 1);
});

tenant_cache_forget('integration:logo');
```

### Cache Key Examples

For tenant ID 1:
- `integration:logo` → `tenant:1:integration:logo`
- `pireps:list:all` → `tenant:1:pireps:list:all`
- `users:pilots:analytics` → `tenant:1:users:pilots:analytics`

For tenant ID 2:
- `integration:logo` → `tenant:2:integration:logo`
- `pireps:list:all` → `tenant:2:pireps:list:all`
- `users:pilots:analytics` → `tenant:2:users:pilots:analytics`

## Updated Controllers

The following controllers have been updated to use tenant-aware caching:

### Integration Module
- `LogoController.php` - Logo management
- `IntegrationController.php` - Custom fields and options
- `RanksController.php` - Rank management

### Events Module
- `EventsController.php` - Event management

### Pirep Module
- `PirepController.php` - Pirep management

### Routes Module
- `RoutesController.php` - Route management

### Users Module
- `UsersController.php` - Pilot management

## Testing

Use the provided test command to verify tenant-aware caching:

```bash
# Test all tenants
php artisan test:tenant-cache

# Test specific tenant
php artisan test:tenant-cache va1.rotate.com
```

The test command will:
1. Generate tenant-specific cache keys
2. Test cache remember/forget operations
3. Verify cache isolation between tenants
4. Clean up test data

## Benefits

### Data Isolation
- Each tenant's cached data is completely isolated
- No risk of data leakage between tenants
- Proper multi-tenant architecture compliance

### Performance
- Reduced database queries through caching
- Tenant-specific cache invalidation
- No cache pollution between tenants

### Maintainability
- Consistent caching pattern across all controllers
- Easy to understand and debug
- Centralized cache key management

## Migration Notes

### For New Controllers
When adding caching to new controllers:

1. Import the helper functions:
```php
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;
```

2. Replace `Cache::store('redis')->remember()` with `tenant_cache_remember()`
3. Replace `Cache::store('redis')->forget()` with `tenant_cache_forget()`

### For Existing Code
If you have existing code using direct cache operations:

```php
// Old way
Cache::store('redis')->remember('key', 1800, $callback);
Cache::store('redis')->forget('key');

// New way
tenant_cache_remember('key', 1800, $callback);
tenant_cache_forget('key');
```

## Configuration

The tenant-aware caching system uses the existing Redis cache configuration from `config/cache.php`. No additional configuration is required.

## Troubleshooting

### Common Issues

1. **Cache keys not being prefixed**: Ensure the tenant is properly identified in the middleware
2. **Helper functions not found**: Run `composer dump-autoload` to register the helper functions
3. **Cache not working in console commands**: The system falls back to null tenant ID for console commands

### Debugging

Check the current tenant:
```php
if (app()->bound('currentTenant')) {
    $tenant = app('currentTenant');
    dd($tenant->id, $tenant->domain);
}
```

Check cache key generation:
```php
$key = get_tenant_cache_key('test:key');
dd($key); // Should show tenant:ID:test:key
```

## Security Considerations

- Each tenant's cache is completely isolated
- No cross-tenant cache access is possible
- Cache keys are automatically prefixed to prevent collisions
- Tenant identification is handled at the middleware level

## Performance Considerations

- Cache operations are lightweight and efficient
- Tenant ID lookup is cached at the application level
- No additional database queries for cache key generation
- Redis operations remain fast with tenant prefixes

## Future Enhancements

1. **Cache Warming**: Implement tenant-specific cache warming strategies
2. **Cache Analytics**: Add monitoring for tenant-specific cache hit rates
3. **Cache Partitioning**: Implement Redis database partitioning per tenant
4. **Cache Compression**: Add compression for large cached objects

## File Structure

```
src/
├── app/
│   ├── Helpers/
│   │   └── TenantCacheHelper.php          # Helper functions
│   └── Console/Commands/
│       └── TestTenantCache.php            # Test command
├── Modules/
│   ├── Integration/Http/Controllers/      # Updated controllers
│   ├── Events/Http/Controllers/           # Updated controllers
│   ├── Pirep/Http/Controllers/            # Updated controllers
│   ├── Routes/Http/Controllers/           # Updated controllers
│   └── Users/Http/Controllers/            # Updated controllers
└── TENANT_CACHING_GUIDE.md               # This guide
```

## Conclusion

The tenant-aware caching system provides a robust, secure, and performant solution for multi-tenant caching. All controllers in the Modules folder now use tenant-specific cache keys, ensuring proper data isolation and preventing any potential data leakage between tenants. 