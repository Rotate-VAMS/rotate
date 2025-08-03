<?php

namespace App\Console\Commands;

use App\Helpers\RotateConstants;
use Illuminate\Console\Command;
use App\Models\Tenant;
use App\Models\User;
use App\Models\FlightType;
use App\Models\DiscordSettings;
use App\Models\SystemSettings;
use App\Models\Leaderboard;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use function App\Helpers\set_permissions_team_id;

class RegisterTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:register 
                            {name : The name of the tenant}
                            {domain : The domain for the tenant}
                            {--admin-email=admin@example.com : Admin email address}
                            {--admin-password=12345678 : Admin password}
                            {--admin-callsign=Admin : Admin callsign}
                            {--force : Force registration even if domain exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a new tenant and seed all necessary data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $domain = $this->argument('domain');
        $adminEmail = $this->option('admin-email');
        $adminPassword = $this->option('admin-password');
        $adminCallsign = $this->option('admin-callsign');
        $force = $this->option('force');

        // Validate inputs
        $validator = Validator::make([
            'name' => $name,
            'domain' => $domain,
            'admin_email' => $adminEmail,
            'admin_password' => $adminPassword,
        ], [
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255|regex:/^[a-zA-Z0-9.-]+$/',
            'admin_email' => 'required|email|max:255',
            'admin_password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            $this->error('Validation failed:');
            foreach ($validator->errors()->all() as $error) {
                $this->error("- {$error}");
            }
            return 1;
        }

        // Check if tenant already exists
        $existingTenant = Tenant::where('domain', $domain)->first();
        if ($existingTenant && !$force) {
            $this->error("Tenant with domain '{$domain}' already exists.");
            $this->error("Use --force flag to override or choose a different domain.");
            return 1;
        }

        if ($existingTenant && $force) {
            $this->warn("Tenant with domain '{$domain}' already exists. Overwriting...");
            $this->deleteTenantData($existingTenant);
        }

        try {
            $this->info("Creating tenant: {$name} ({$domain})");
            
            // Create tenant
            $tenant = Tenant::create([
                'name' => $name,
                'domain' => $domain,
                'plan_key' => 'free',
                'admin_email' => $adminEmail,
                'admin_password' => $adminPassword,
                'admin_callsign' => $adminCallsign,
            ]);

            $this->info("✅ Tenant created successfully (ID: {$tenant->id})");

            // Seed tenant data
            $adminUser = $this->seedTenantData($tenant, $adminEmail, $adminPassword, $adminCallsign);

            

            $this->info("✅ Tenant registration completed successfully!");
            $this->info("Domain: {$domain}");
            $this->info("Admin Email: {$adminEmail}");
            $this->info("Admin Password: {$adminPassword}");
            
            return 0;

        } catch (\Exception $e) {
            $this->error("Failed to register tenant: " . $e->getMessage());
            
            // Clean up if tenant was created but seeding failed
            if (isset($tenant)) {
                $this->deleteTenantData($tenant);
            }
            
            return 1;
        }
    }

    /**
     * Seed all necessary data for a tenant
     */
    private function seedTenantData(Tenant $tenant, string $adminEmail, string $adminPassword, string $adminCallsign): User
    {
        // Set the tenant context for all operations
        app()->instance('currentTenant', $tenant);
        set_permissions_team_id($tenant->id);

        $this->info("Seeding tenant data...");

        // 1. Create roles
        $this->createRoles($tenant);

        // 2. Create permissions
        $this->createPermissions($tenant);

        // 3. Assign permissions to roles
        $this->assignPermissionsToRoles($tenant);

        // 4. Create admin user
        $adminUser = $this->createAdminUser($tenant, $adminEmail, $adminPassword, $adminCallsign);

        // 5. Create flight types
        $this->createFlightTypes($tenant);

        // 6. Create Discord settings
        $this->createDiscordSettings($tenant);

        // 7. Create system settings
        $this->createSystemSettings($tenant);

        // 8. Create leaderboard settings
        $this->createLeaderboardSettings($tenant);

        $this->info("✅ Tenant data seeded successfully");
        
        return $adminUser;
    }

    /**
     * Create default roles for the tenant
     */
    private function createRoles(Tenant $tenant): void
    {
        $this->line("Creating roles...");

        $roles = [
            ['name' => 'admin', 'guard_name' => 'web'],
            ['name' => 'pilot', 'guard_name' => 'web'],
        ];

        foreach ($roles as $roleData) {
            // Use Spatie's team-aware role creation
            $roleModel = new Role();
            $roleModel->name = $roleData['name'];
            $roleModel->guard_name = $roleData['guard_name'];
            $roleModel->tenant_id = $tenant->id;
            $roleModel->save();
            $this->line("  - Created role: {$roleData['name']}");
        }
    }

    /**
     * Create default permissions for the tenant
     */
    private function createPermissions(Tenant $tenant): void
    {
        $this->line("Creating permissions...");

        $permissions = [
            // Pirep permissions
            'view-all-pirep',
            'create-pirep',
            'edit-all-pirep',
            'delete-all-pirep',
            'edit-own-pirep',
            'delete-own-pirep',

            // Route permissions
            'view-route',
            'create-route',
            'edit-route',
            'delete-route',

            // Event permissions
            'view-event',
            'create-event',
            'edit-event',
            'delete-event',

            // User permissions
            'view-user',
            'create-user',
            'edit-user',
            'delete-user',

            // Settings permissions
            'access-settings',
        ];

        foreach ($permissions as $permission) {
            // Use Spatie's team-aware permission creation
            $permissionModel = new Permission();
            $permissionModel->name = $permission;
            $permissionModel->guard_name = 'web';
            $permissionModel->tenant_id = $tenant->id;
            $permissionModel->save();
            $this->line("  - Created permission: {$permission}");
        }
    }

    /**
     * Assign permissions to roles
     */
    private function assignPermissionsToRoles(Tenant $tenant): void
    {
        $this->line("Assigning permissions to roles...");

        $adminRole = Role::where('name', 'admin')->where('tenant_id', $tenant->id)->first();
        $pilotRole = Role::where('name', 'pilot')->where('tenant_id', $tenant->id)->first();

        // Admin gets all permissions
        $adminRole->givePermissionTo(Permission::where('tenant_id', $tenant->id)->get());
        $this->line("  - Assigned all permissions to admin role");

        // Pilot gets limited permissions
        $pilotPermissionsArray = [
            'view-all-pirep',
            'create-pirep',
            'edit-own-pirep',
            'delete-own-pirep',
            'view-route',
            'view-event',
            'view-user',
        ];

        $pilotPermissions = Permission::where('tenant_id', $tenant->id)->whereIn('name', $pilotPermissionsArray)->get();

        $pilotRole->givePermissionTo($pilotPermissions);
        $this->line("  - Assigned pilot permissions to pilot role");
    }

    /**
     * Create admin user for the tenant
     */
    private function createAdminUser(Tenant $tenant, string $email, string $password, string $callsign): User
    {
        $this->line("Creating admin user...");

        $user = User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
            'callsign' => $callsign,
            'status' => User::PILOT_STATUS_ACTIVE,
            'flying_hours' => 150,
            'tenant_id' => $tenant->id,
        ]);

        $adminRole = Role::where('name', 'admin')->where('tenant_id', $tenant->id)->first();
        $user->assignRole($adminRole);

        $this->line("  - Created admin user: {$email}");
        
        return $user;
    }

    /**
     * Create default flight types for the tenant
     */
    private function createFlightTypes(Tenant $tenant): void
    {
        $this->line("Creating flight types...");

        $flightTypes = [
            ['flight_type' => 'Regular', 'multiplier' => 1]
        ];

        foreach ($flightTypes as $flightTypeData) {
            FlightType::create([
                'flight_type' => $flightTypeData['flight_type'],
                'multiplier' => $flightTypeData['multiplier'],
                'tenant_id' => $tenant->id,
            ]);
            $this->line("  - Created flight type: {$flightTypeData['flight_type']}");
        }
    }

    /**
     * Create Discord settings for the tenant
     */
    private function createDiscordSettings(Tenant $tenant): void
    {
        $this->line("Creating Discord settings...");

        DiscordSettings::create([
            'setting_key' => 'discord_bot_event_activity',
            'setting_value' => DiscordSettings::DISCORD_EVENT_ACTIVITY_DISABLED,
            'tenant_id' => $tenant->id,
        ]);

        $this->line("  - Created Discord settings");
    }

    /**
     * Create system settings for the tenant
     */
    private function createSystemSettings(Tenant $tenant): void
    {
        $this->line("Creating system settings...");

        SystemSettings::create([
            'key' => SystemSettings::LEADERBOARD_POINTS_CONFIGURATION,
            'value' => SystemSettings::SETTING_ENABLED,
            'tenant_id' => $tenant->id,
        ]);

        $this->line("  - Created system settings");
    }

    /**
     * Create leaderboard settings for the tenant
     */
    private function createLeaderboardSettings(Tenant $tenant): void
    {
        $this->line("Creating leaderboard settings...");

        $leaderboardEvents = Leaderboard::getLeaderboardEvents();

        foreach ($leaderboardEvents as $leaderboardEvent) {

            Leaderboard::create([
                'tenant_id' => $tenant->id,
                'leaderboard_event_name' => $leaderboardEvent,
                'points' => RotateConstants::CONSTANT_FOR_ONE,
            ]);

            $this->line("  - Created leaderboard setting: {$leaderboardEvent}");
        }

        $this->line("  - Created leaderboard settings");
    }



    /**
     * Delete all data for a tenant
     */
    private function deleteTenantData(Tenant $tenant): void
    {
        $this->warn("Deleting existing tenant data...");

        // Set tenant context
        app()->instance('currentTenant', $tenant);
        set_permissions_team_id($tenant->id);

        // Delete in reverse order of dependencies
        User::where('tenant_id', $tenant->id)->delete();
        FlightType::where('tenant_id', $tenant->id)->delete();
        DiscordSettings::where('tenant_id', $tenant->id)->delete();
        SystemSettings::where('tenant_id', $tenant->id)->delete();
        Leaderboard::where('tenant_id', $tenant->id)->delete();
        // Delete roles and permissions (this will cascade to role_has_permissions)
        Role::where('tenant_id', $tenant->id)->delete();
        Permission::where('tenant_id', $tenant->id)->delete();

        // Finally delete the tenant
        $tenant->delete();

        $this->warn("✅ Tenant data deleted");
    }
} 