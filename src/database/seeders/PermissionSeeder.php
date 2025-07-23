<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $pirepPermissions = [
            'view-pirep',
            'create-pirep',
            'edit-pirep',
            'delete-pirep',
        ];

        $routesPermissions = [
            'view-route',
            'create-route',
            'edit-route',
            'delete-route',
        ];

        $eventsPermissions = [
            'view-event',
            'create-event',
            'edit-event',
            'delete-event',
        ];

        $userPermissions = [
            'view-user',
            'create-user',
            'edit-user',
            'delete-user',
        ];

        $settingsPermissions = [
            'access-settings',
        ];
        
        foreach ($pirepPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach ($routesPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach ($eventsPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        foreach ($userPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }
        
        foreach ($settingsPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->givePermissionTo(Permission::all());

        $pilotRole = Role::where('name', 'pilot')->first();
        $pilotRole->givePermissionTo([
            'view-pirep',
            'view-route',
            'view-event',
            'view-user',
        ]);
    }
}