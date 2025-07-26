# Tenant-Specific Storage URLs Guide

This guide explains how to use the tenant-specific storage URL system in your multi-tenant Laravel application.

## Overview

The system automatically generates tenant-specific URLs for storage files based on the current tenant's domain. This allows each tenant to have their own domain-specific URLs for uploaded files, logos, and other assets.

## How It Works

1. **Tenant Identification**: The `IdentifyTenant` middleware identifies the current tenant based on the request domain
2. **Dynamic URL Generation**: The `TenantStorageService` generates URLs based on the current tenant's domain
3. **Automatic Integration**: Storage URLs are automatically tenant-specific when using the provided helper functions

## Configuration

### Environment Setup

For development, you can use local domains like:
- `va1.localhost`
- `va2.localhost`
- `client1.rotate.com`
- `client2.rotate.com`

For production, use your actual domains:
- `va1.rotate.com`
- `va2.rotate.com`

### Database Setup

Make sure your tenants table has the correct domains:

```sql
INSERT INTO tenants (name, domain) VALUES 
('VA 1', 'va1.rotate.com'),
('VA 2', 'va2.rotate.com'),
('Development', 'dev.rotate.com');
```

## Usage

### Helper Functions

The system provides several helper functions for generating tenant-specific URLs:

```php
// Get tenant-specific storage URL
$storageUrl = tenant_storage_url('documents/logo.png');
// Result: https://va1.rotate.com/storage/documents/logo.png

// Get tenant-specific asset URL
$assetUrl = tenant_asset_url('images/logo.png');
// Result: https://va1.rotate.com/asset/images/logo.png

// Get tenant-specific URL
$url = tenant_url('dashboard');
// Result: https://va1.rotate.com/dashboard
```

### In Models

When working with documents or files in models:

```php
use function App\Helpers\tenant_storage_url;

class Document extends Model
{
    public function getUrlAttribute()
    {
        return tenant_storage_url($this->file_path);
    }
}
```

### In Controllers

```php
use function App\Helpers\tenant_storage_url;

class FileController extends Controller
{
    public function download($file)
    {
        $url = tenant_storage_url("files/{$file}");
        return response()->json(['url' => $url]);
    }
}
```

### In Blade Templates

```php
<img src="{{ tenant_storage_url('logos/company-logo.png') }}" alt="Logo">
<a href="{{ tenant_url('dashboard') }}">Dashboard</a>
```

### In Vue Components

The backend automatically provides tenant-specific URLs through the API endpoints. For example, the logo fetching in `AppLayout.vue` automatically uses tenant-specific URLs.

## Testing

Use the provided test command to verify tenant-specific URLs:

```bash
# Test all tenants
php artisan test:tenant-storage

# Test specific tenant
php artisan test:tenant-storage va1.rotate.com
```

## File Structure

```
src/
├── app/
│   ├── Services/
│   │   └── TenantStorageService.php          # Core service for URL generation
│   ├── Providers/
│   │   └── TenantStorageServiceProvider.php  # Service provider registration
│   ├── Helpers/
│   │   └── TenantStorageHelper.php           # Helper functions
│   └── Console/Commands/
│       └── TestTenantStorage.php             # Test command
└── config/
    └── filesystems.php                       # Updated with tenant support
```

## Migration from Static URLs

If you have existing code using static storage URLs, replace:

```php
// Old way
Storage::disk('public')->url('file.jpg')

// New way
tenant_storage_url('file.jpg')
```

## Development vs Production

### Development
- Uses local domains like `va1.localhost`
- URLs: `http://va1.localhost/storage/file.jpg`

### Production
- Uses actual domains like `va1.rotate.com`
- URLs: `https://va1.rotate.com/storage/file.jpg`

## Troubleshooting

### Common Issues

1. **URLs still showing APP_URL**: Make sure the tenant is properly identified in the middleware
2. **Console commands not working**: The system falls back to APP_URL for console commands
3. **Helper functions not found**: Run `composer dump-autoload` to register the helper functions

### Debugging

Check the current tenant:
```php
if (app()->bound('currentTenant')) {
    $tenant = app('currentTenant');
    dd($tenant->domain, $tenant->getBaseUrl());
}
```

## Security Considerations

- Each tenant's files are isolated by the tenant identification system
- URLs are generated dynamically based on the current tenant
- No cross-tenant file access is possible through the URL system

## Performance

- URL generation is cached at the application level
- No database queries are performed for URL generation after initial tenant identification
- Helper functions are lightweight and efficient 