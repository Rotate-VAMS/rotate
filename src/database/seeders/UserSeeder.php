<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $user = new User();
        $user->name = 'Admin';
        $user->email = 'admin@example.com';
        $user->password = Hash::make('12345678');
        $user->callsign = 'Admin';
        $user->status = 1;
        $user->flying_hours = 150;
        $user->tenant_id = 1;
        $user->save();

        $user->assignRole('admin');
    }
}