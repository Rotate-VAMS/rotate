<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use function App\Helpers\get_permissions_team_id;
use function App\Helpers\set_permissions_team_id;

class TestTenantPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tenant-permissions {domain?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tenant-aware roles and permissions functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        
        if ($domain) {
            $tenant = Tenant::where('domain', $domain)->first();
            if (!$tenant) {
                $this->error("Tenant with domain '{$domain}' not found.");
                return 1;
            }
            
            $this->testTenant($tenant);
        } else {
            $tenants = Tenant::all();
            foreach ($tenants as $tenant) {
                $this->testTenant($tenant);
                $this->line('');
            }
        }
        
        return 0;
    }
    
    private function testTenant(Tenant $tenant)
    {
        $this->info("Testing tenant: {$tenant->name} ({$tenant->domain})");
        
        // Simulate the tenant being the current tenant
        app()->instance('currentTenant', $tenant);
        set_permissions_team_id($tenant->id);
        
        $this->line("Current permissions team ID: " . get_permissions_team_id());
        
        // Test role creation
        $roleName = "test_role_{$tenant->id}";
        $role = Role::firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $this->line("Created/found role: {$role->name} (ID: {$role->id})");
        $this->line("Role tenant_id: {$role->tenant_id}");
        
        // Test permission creation
        $permissionName = "test_permission_{$tenant->id}";
        $permission = Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
            'tenant_id' => $tenant->id,
        ]);
        $this->line("Created/found permission: {$permission->name} (ID: {$permission->id})");
        $this->line("Permission tenant_id: {$permission->tenant_id}");
        
        // Test role-permission assignment
        $role->givePermissionTo($permission);
        $this->line("Assigned permission '{$permission->name}' to role '{$role->name}'");
        
        // Test that roles and permissions are tenant-isolated
        $this->line("Tenant isolation test:");
        $tenant1 = Tenant::first();
        $tenant2 = Tenant::skip(1)->first();
        
        if ($tenant1 && $tenant2) {
            // Switch to tenant 1
            app()->instance('currentTenant', $tenant1);
            set_permissions_team_id($tenant1->id);
            $roles1 = Role::all();
            $permissions1 = Permission::all();
            
            // Switch to tenant 2
            app()->instance('currentTenant', $tenant2);
            set_permissions_team_id($tenant2->id);
            $roles2 = Role::all();
            $permissions2 = Permission::all();
            
            $this->line("Tenant 1 roles count: {$roles1->count()}");
            $this->line("Tenant 2 roles count: {$roles2->count()}");
            $this->line("Tenant 1 permissions count: {$permissions1->count()}");
            $this->line("Tenant 2 permissions count: {$permissions2->count()}");
            
            // Clean up test data
            $this->cleanupTestData($tenant1);
            $this->cleanupTestData($tenant2);
        }
    }
    
    private function cleanupTestData(Tenant $tenant)
    {
        app()->instance('currentTenant', $tenant);
        set_permissions_team_id($tenant->id);
        
        // Clean up test roles and permissions
        Role::where('name', 'like', 'test_role_%')->delete();
        Permission::where('name', 'like', 'test_permission_%')->delete();
    }
} 