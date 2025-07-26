<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use App\Models\FlightType;
use App\Models\DiscordSettings;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ListTenants extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:list {--detailed : Show detailed information for each tenant}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all tenants and their status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tenants = Tenant::all();
        
        if ($tenants->isEmpty()) {
            $this->info('No tenants found.');
            return 0;
        }

        $this->info("Found {$tenants->count()} tenant(s):");
        $this->line('');

        foreach ($tenants as $tenant) {
            $this->displayTenantInfo($tenant);
            
            if ($this->option('detailed')) {
                $this->displayTenantDetails($tenant);
            }
            
            $this->line('');
        }

        return 0;
    }

    /**
     * Display basic tenant information
     */
    private function displayTenantInfo(Tenant $tenant): void
    {
        $this->info("📋 Tenant: {$tenant->name}");
        $this->line("   Domain: {$tenant->domain}");
        $this->line("   ID: {$tenant->id}");
        $this->line("   Created: {$tenant->created_at->format('Y-m-d H:i:s')}");
    }

    /**
     * Display detailed tenant information
     */
    private function displayTenantDetails(Tenant $tenant): void
    {
        // Set tenant context for accurate counts
        app()->instance('currentTenant', $tenant);
        
        // Get counts for this tenant
        $userCount = User::where('tenant_id', $tenant->id)->count();
        $roleCount = Role::where('tenant_id', $tenant->id)->count();
        $permissionCount = Permission::where('tenant_id', $tenant->id)->count();
        $flightTypeCount = FlightType::where('tenant_id', $tenant->id)->count();
        $discordSettingsCount = DiscordSettings::where('tenant_id', $tenant->id)->count();

        $this->line("   📊 Statistics:");
        $this->line("      Users: {$userCount}");
        $this->line("      Roles: {$roleCount}");
        $this->line("      Permissions: {$permissionCount}");
        $this->line("      Flight Types: {$flightTypeCount}");
        $this->line("      Discord Settings: {$discordSettingsCount}");

        // Show admin users
        $adminUsers = User::where('tenant_id', $tenant->id)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->get(['name', 'email', 'callsign']);

        if ($adminUsers->isNotEmpty()) {
            $this->line("   👑 Admin Users:");
            foreach ($adminUsers as $admin) {
                $this->line("      - {$admin->name} ({$admin->email}) - {$admin->callsign}");
            }
        }

        // Show roles
        $roles = Role::where('tenant_id', $tenant->id)->get(['name']);
        if ($roles->isNotEmpty()) {
            $this->line("   🎭 Roles:");
            foreach ($roles as $role) {
                $this->line("      - {$role->name}");
            }
        }

        // Show flight types
        $flightTypes = FlightType::where('tenant_id', $tenant->id)->get(['flight_type', 'multiplier']);
        if ($flightTypes->isNotEmpty()) {
            $this->line("   ✈️ Flight Types:");
            foreach ($flightTypes as $flightType) {
                $this->line("      - {$flightType->flight_type} (Multiplier: {$flightType->multiplier})");
            }
        }
    }
} 