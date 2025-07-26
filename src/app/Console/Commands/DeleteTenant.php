<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use App\Models\FlightType;
use App\Models\DiscordSettings;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use function App\Helpers\set_permissions_team_id;

class DeleteTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:delete 
                            {domain : The domain of the tenant to delete}
                            {--force : Force deletion without confirmation}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a tenant and all associated data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $domain = $this->argument('domain');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        // Find the tenant
        $tenant = Tenant::where('domain', $domain)->first();
        
        if (!$tenant) {
            $this->error("Tenant with domain '{$domain}' not found.");
            return 1;
        }

        $this->info("Found tenant: {$tenant->name} ({$domain})");

        if ($dryRun) {
            $this->showDeletionPreview($tenant);
            return 0;
        }

        // Confirm deletion unless forced
        if (!$force) {
            if (!$this->confirm("Are you sure you want to delete tenant '{$tenant->name}' and ALL its data?")) {
                $this->info('Deletion cancelled.');
                return 0;
            }

            if (!$this->confirm("This will permanently delete ALL data for this tenant. This action cannot be undone. Continue?")) {
                $this->info('Deletion cancelled.');
                return 0;
            }
        }

        try {
            $this->deleteTenantData($tenant);
            $this->info("✅ Tenant '{$tenant->name}' deleted successfully!");
            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to delete tenant: " . $e->getMessage());
            return 1;
        }
    }

    /**
     * Show what would be deleted without actually deleting
     */
    private function showDeletionPreview(Tenant $tenant): void
    {
        $this->info("🔍 DRY RUN - Preview of what would be deleted:");
        $this->line('');

        // Set tenant context for accurate counts
        app()->instance('currentTenant', $tenant);
        set_permissions_team_id($tenant->id);

        // Count data for this tenant
        $userCount = User::where('tenant_id', $tenant->id)->count();
        $roleCount = Role::where('tenant_id', $tenant->id)->count();
        $permissionCount = Permission::where('tenant_id', $tenant->id)->count();
        $flightTypeCount = FlightType::where('tenant_id', $tenant->id)->count();
        $discordSettingsCount = DiscordSettings::where('tenant_id', $tenant->id)->count();

        $this->line("📊 Data to be deleted:");
        $this->line("   - Users: {$userCount}");
        $this->line("   - Roles: {$roleCount}");
        $this->line("   - Permissions: {$permissionCount}");
        $this->line("   - Flight Types: {$flightTypeCount}");
        $this->line("   - Discord Settings: {$discordSettingsCount}");
        $this->line("   - Tenant record: 1");

        // Show some sample data
        $this->line('');
        $this->line("📋 Sample data to be deleted:");

        // Sample users
        $users = User::where('tenant_id', $tenant->id)->take(3)->get(['name', 'email']);
        if ($users->isNotEmpty()) {
            $this->line("   Users:");
            foreach ($users as $user) {
                $this->line("     - {$user->name} ({$user->email})");
            }
            if ($userCount > 3) {
                $this->line("     ... and " . ($userCount - 3) . " more users");
            }
        }

        // Sample roles
        $roles = Role::where('tenant_id', $tenant->id)->get(['name']);
        if ($roles->isNotEmpty()) {
            $this->line("   Roles:");
            foreach ($roles as $role) {
                $this->line("     - {$role->name}");
            }
        }

        // Sample flight types
        $flightTypes = FlightType::where('tenant_id', $tenant->id)->get(['flight_type']);
        if ($flightTypes->isNotEmpty()) {
            $this->line("   Flight Types:");
            foreach ($flightTypes as $flightType) {
                $this->line("     - {$flightType->flight_type}");
            }
        }

        $this->line('');
        $this->warn("⚠️  This action cannot be undone!");
    }

    /**
     * Delete all data for a tenant
     */
    private function deleteTenantData(Tenant $tenant): void
    {
        $this->info("🗑️  Deleting tenant data...");

        // Set tenant context
        app()->instance('currentTenant', $tenant);
        set_permissions_team_id($tenant->id);

        // Delete in reverse order of dependencies to avoid foreign key constraints
        $this->line("Deleting users...");
        $userCount = User::where('tenant_id', $tenant->id)->count();
        User::where('tenant_id', $tenant->id)->delete();
        $this->line("  - Deleted {$userCount} users");

        $this->line("Deleting flight types...");
        $flightTypeCount = FlightType::where('tenant_id', $tenant->id)->count();
        FlightType::where('tenant_id', $tenant->id)->delete();
        $this->line("  - Deleted {$flightTypeCount} flight types");

        $this->line("Deleting Discord settings...");
        $discordSettingsCount = DiscordSettings::where('tenant_id', $tenant->id)->count();
        DiscordSettings::where('tenant_id', $tenant->id)->delete();
        $this->line("  - Deleted {$discordSettingsCount} Discord settings");

        $this->line("Deleting roles and permissions...");
        $roleCount = Role::where('tenant_id', $tenant->id)->count();
        $permissionCount = Permission::where('tenant_id', $tenant->id)->count();
        
        // Delete roles and permissions (this will cascade to role_has_permissions)
        Role::where('tenant_id', $tenant->id)->delete();
        Permission::where('tenant_id', $tenant->id)->delete();
        
        $this->line("  - Deleted {$roleCount} roles");
        $this->line("  - Deleted {$permissionCount} permissions");

        $this->line("Deleting tenant record...");
        $tenant->delete();
        $this->line("  - Deleted tenant record");

        $this->info("✅ Tenant data deleted successfully");
    }
} 