# Migration Examples: Static URLs to Tenant-Specific URLs

This document provides practical examples of how to migrate existing code from static storage URLs to tenant-specific URLs.

## Before and After Examples

### 1. Document Model

**Before:**
```php
class Document extends Model
{
    public function getUrlAttribute()
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
```

**After:**
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

### 2. Controller Method

**Before:**
```php
class FileController extends Controller
{
    public function download($file)
    {
        $url = Storage::disk('public')->url("files/{$file}");
        return response()->json(['url' => $url]);
    }
}
```

**After:**
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

### 3. Blade Template

**Before:**
```php
<img src="{{ Storage::disk('public')->url('logos/company-logo.png') }}" alt="Logo">
```

**After:**
```php
<img src="{{ tenant_storage_url('logos/company-logo.png') }}" alt="Logo">
```

### 4. Vue Component (Backend API)

**Before:**
```php
// In controller
public function getLogo()
{
    $logoPath = Storage::disk('public')->url('logos/logo.png');
    return response()->json(['logo' => $logoPath]);
}
```

**After:**
```php
// In controller
use function App\Helpers\tenant_storage_url;

public function getLogo()
{
    $logoPath = tenant_storage_url('logos/logo.png');
    return response()->json(['logo' => $logoPath]);
}
```

### 5. Event Listener

**Before:**
```php
class SendEmailNotification
{
    public function handle($event)
    {
        $attachmentUrl = Storage::disk('public')->url('attachments/file.pdf');
        // Send email with attachment URL
    }
}
```

**After:**
```php
use function App\Helpers\tenant_storage_url;

class SendEmailNotification
{
    public function handle($event)
    {
        $attachmentUrl = tenant_storage_url('attachments/file.pdf');
        // Send email with attachment URL
    }
}
```

### 6. Service Class

**Before:**
```php
class FileService
{
    public function generateDownloadLink($file)
    {
        return Storage::disk('public')->url("downloads/{$file}");
    }
}
```

**After:**
```php
use function App\Helpers\tenant_storage_url;

class FileService
{
    public function generateDownloadLink($file)
    {
        return tenant_storage_url("downloads/{$file}");
    }
}
```

## Common Patterns to Replace

### Pattern 1: Direct Storage URL
```php
// Replace this:
Storage::disk('public')->url($path)

// With this:
tenant_storage_url($path)
```

### Pattern 2: Asset URLs
```php
// Replace this:
asset('images/logo.png')

// With this:
tenant_asset_url('images/logo.png')
```

### Pattern 3: Application URLs
```php
// Replace this:
url('dashboard')

// With this:
tenant_url('dashboard')
```

## Testing Your Changes

After making changes, test with different tenants:

```bash
# Test with tenant 1
php artisan test:tenant-storage va1.rotate.com

# Test with tenant 2  
php artisan test:tenant-storage va2.rotate.com
```

## Verification Checklist

- [ ] All `Storage::disk('public')->url()` calls replaced with `tenant_storage_url()`
- [ ] All `asset()` calls for tenant-specific assets replaced with `tenant_asset_url()`
- [ ] All `url()` calls for tenant-specific routes replaced with `tenant_url()`
- [ ] Helper functions imported in all files where used
- [ ] Tests pass for multiple tenants
- [ ] URLs generate correctly for each tenant domain 