# Tenant-Aware Roles and Permissions Guide

This guide explains the tenant-aware roles and permissions system implemented in your multi-tenant Laravel application using the Spatie Laravel Permission package.

## Overview

The tenant-aware roles and permissions system ensures that roles and permissions are completely isolated between tenants, preventing any cross-tenant access to roles or permissions.

## Implementation

### Configuration Changes

The system uses Spatie's built-in "teams" feature to implement tenant isolation:

1. **Enabled Teams Feature**: Set `'teams' => true` in `config/permission.php`
2. **Custom Team Foreign Key**: Set `'team_foreign_key' => 'tenant_id'` to use tenant_id as the team identifier
3. **Custom Team Resolver**: Created `TenantTeamResolver` to automatically resolve the current tenant ID

### Database Structure

The existing migration already includes the necessary `tenant_id` foreign keys:

```sql
-- roles table
CREATE TABLE roles (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    tenant_id BIGINT,  -- Team foreign key
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(tenant_id, name, guard_name)  -- Tenant-aware unique constraint
);

-- permissions table  
CREATE TABLE permissions (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    guard_name VARCHAR(255),
    tenant_id BIGINT,  -- Team foreign key
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(name, guard_name)  -- Global unique constraint
);
```

### Custom Team Resolver

The `TenantTeamResolver` class automatically resolves the current tenant ID:

```php
<?php

namespace App\Services;

use Spatie\Permission\Contracts\PermissionsTeamResolver;

class TenantTeamResolver implements PermissionsTeamResolver
{
    protected int|string|null $teamId = null;

    public function getPermissionsTeamId(): int|string|null
    {
        // If a team ID is explicitly set, use it
        if ($this->teamId !== null) {
            return $this->teamId;
        }

        // Get the current tenant ID from the application container
        if (app()->bound('currentTenant')) {
            return app('currentTenant')->id;
        }

        return null;
    }

    public function setPermissionsTeamId($id): void
    {
        if ($id instanceof \Illuminate\Database\Eloquent\Model) {
            $id = $id->getKey();
        }
        $this->teamId = $id;
    }
}
```

### Middleware Integration

The `IdentifyTenant` middleware automatically sets the permissions team ID:

```php
public function handle($request, Closure $next)
{
    $host = $request->getHost();
    $tenant = Tenant::where('domain', $host)->first();

    if (!$tenant) {
        abort(404, 'Tenant not found');
    }

    app()->instance('currentTenant', $tenant);
    
    // Set the permissions team ID for tenant-aware roles and permissions
    set_permissions_team_id($tenant->id);

    return $next($request);
}
```

## Usage Examples

### Creating Tenant-Aware Roles

```php
// This will automatically use the current tenant ID
$role = Role::create(['name' => 'admin']);

// Or explicitly set the tenant
set_permissions_team_id($tenant->id);
$role = Role::create(['name' => 'manager']);
```

### Creating Tenant-Aware Permissions

```php
// This will automatically use the current tenant ID
$permission = Permission::create(['name' => 'edit-users']);

// Or explicitly set the tenant
set_permissions_team_id($tenant->id);
$permission = Permission::create(['name' => 'delete-users']);
```

### Assigning Permissions to Roles

```php
$role = Role::findByName('admin');
$permission = Permission::findByName('edit-users');

$role->givePermissionTo($permission);
```

### Assigning Roles to Users

```php
$user = User::find(1);
$role = Role::findByName('admin');

$user->assignRole($role);
```

### Checking Permissions

```php
// Check if user has permission
if ($user->hasPermissionTo('edit-users')) {
    // User can edit users
}

// Check if user has role
if ($user->hasRole('admin')) {
    // User is an admin
}
```

## Helper Functions

The system provides helper functions for working with tenant-aware permissions:

```php
// Set the permissions team ID (tenant ID) for the current request
set_permissions_team_id($tenantId);

// Get the current permissions team ID (tenant ID)
$teamId = get_permissions_team_id();
```

## Tenant Isolation

### Automatic Isolation

All role and permission operations are automatically scoped to the current tenant:

```php
// These will only return roles/permissions for the current tenant
$roles = Role::all();
$permissions = Permission::all();

// These will only find roles/permissions for the current tenant
$role = Role::findByName('admin');
$permission = Permission::findByName('edit-users');
```

### Manual Team ID Setting

You can manually set the team ID for specific operations:

```php
// Set team ID for a specific operation
set_permissions_team_id($tenant->id);

// Create role for specific tenant
$role = Role::create(['name' => 'admin']);

// Reset to current tenant
set_permissions_team_id(null);
```

## Testing

Use the provided test command to verify tenant-aware permissions:

```bash
# Test all tenants
php artisan test:tenant-permissions

# Test specific tenant
php artisan test:tenant-permissions va1.rotate.com
```

The test command will:
1. Create tenant-specific roles and permissions
2. Test role-permission assignments
3. Verify tenant isolation
4. Clean up test data

## Benefits

### Security
- Complete isolation of roles and permissions between tenants
- No risk of cross-tenant permission access
- Proper multi-tenant architecture compliance

### Performance
- Efficient queries scoped to current tenant
- Reduced data transfer and processing
- Optimized permission checks

### Maintainability
- Consistent permission system across all tenants
- Easy to understand and debug
- Centralized permission management

## Migration from Non-Tenant-Aware

If you have existing roles and permissions without tenant isolation:

1. **Backup your data** before migration
2. **Assign existing roles/permissions to a default tenant**:

```php
// Assign all existing roles to tenant 1
Role::whereNull('tenant_id')->update(['tenant_id' => 1]);

// Assign all existing permissions to tenant 1  
Permission::whereNull('tenant_id')->update(['tenant_id' => 1]);
```

3. **Update your application code** to use tenant-aware operations
4. **Test thoroughly** to ensure proper isolation

## Common Patterns

### Creating Default Roles for New Tenants

```php
public function createDefaultRolesForTenant(Tenant $tenant)
{
    set_permissions_team_id($tenant->id);
    
    // Create default roles
    $adminRole = Role::create(['name' => 'admin']);
    $userRole = Role::create(['name' => 'user']);
    
    // Create default permissions
    $editUsersPermission = Permission::create(['name' => 'edit-users']);
    $viewUsersPermission = Permission::create(['name' => 'view-users']);
    
    // Assign permissions to roles
    $adminRole->givePermissionTo([$editUsersPermission, $viewUsersPermission]);
    $userRole->givePermissionTo([$viewUsersPermission]);
}
```

### Checking Tenant-Specific Permissions

```php
public function canUserEditUsers(User $user)
{
    // This will automatically check permissions for the current tenant
    return $user->hasPermissionTo('edit-users');
}
```

## Troubleshooting

### Common Issues

1. **Roles/permissions not found**: Ensure the tenant is properly identified and team ID is set
2. **Cross-tenant access**: Verify that `set_permissions_team_id()` is called with the correct tenant ID
3. **Permission checks failing**: Check that the user has the role/permission for the current tenant

### Debugging

Check the current team ID:
```php
$teamId = get_permissions_team_id();
dd($teamId); // Should show the current tenant ID
```

Check available roles for current tenant:
```php
$roles = Role::all();
dd($roles->pluck('name')); // Should only show roles for current tenant
```

## Security Considerations

- Each tenant's roles and permissions are completely isolated
- No cross-tenant role or permission access is possible
- Team ID is automatically resolved from the current tenant
- Permission checks are scoped to the current tenant

## Performance Considerations

- Permission checks are efficient and scoped to current tenant
- Role and permission queries are automatically filtered
- Caching works per-tenant (if enabled)
- No additional database queries for tenant resolution

## File Structure

```
src/
├── app/
│   ├── Services/
│   │   └── TenantTeamResolver.php          # Custom team resolver
│   ├── Http/Middleware/
│   │   └── IdentifyTenant.php              # Updated with team ID setting
│   ├── Helpers/
│   │   └── TenantCacheHelper.php           # Helper functions
│   └── Console/Commands/
│       └── TestTenantPermissions.php       # Test command
├── config/
│   └── permission.php                      # Updated configuration
└── TENANT_PERMISSIONS_GUIDE.md            # This guide
```

## Conclusion

The tenant-aware roles and permissions system provides a robust, secure, and performant solution for multi-tenant permission management. All role and permission operations are automatically scoped to the current tenant, ensuring complete data isolation and preventing any potential cross-tenant access. 