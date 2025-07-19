<?php

namespace Database\Seeders;

use App\Models\FlightType;
use Illuminate\Database\Seeder;

class FlightTypeSeeder extends Seeder
{
    public function run()
    {
        $flighType = new FlightType();
        $flighType->flight_type = 'Regular';
        $flighType->multiplier = 1;
        $flighType->created_at = now();
        $flighType->updated_at = now();
        $flighType->save();
    }
}