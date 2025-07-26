<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'pilot']);

        $this->call([
            FlightTypeSeeder::class,
            UserSeeder::class,
            PermissionSeeder::class,
            DiscordSettingsSeeder::class,
        ]);
    }
}
