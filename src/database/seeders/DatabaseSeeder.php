<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tenant;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed base tenant
        // Tenant::create(['name' => 'Base', 'domain' => 'base.localhost']);
        // // Seed roles
        // Role::create(['name' => 'admin', 'tenant_id' => 1]);
        // Role::create(['name' => 'pilot', 'tenant_id' => 1]);

        $this->call([
            FlightTypeSeeder::class,
            UserSeeder::class,
            PermissionSeeder::class,
            DiscordSettingsSeeder::class,
        ]);
    }
}
