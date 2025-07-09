<?php

namespace Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Breadcrumbs
        $breadcrumbs = [
            [
                'title' => 'Pilots'
            ]
        ];
        return Inertia::render('Users/Pages/Pilots', ['breadcrumbs' => $breadcrumbs]);
    }

    public function jxFetchPilots()
    {
        // $pilots = User::where('role', 'pilot')->get();
        // $pilots = $pilots->map(function ($pilot) {
        //     return [
        //         'id' => $pilot->id,
        //         'name' => $pilot->name,
        //         'callsign' => $pilot->callsign,
        //         'email' => $pilot->email,   
        //         'rank' => $pilot->rank_id ? 'Rank ' . $pilot->rank_id : 'Trainee',
        //         'status' => $pilot->status ?? 'Active',
        //         'flights' => 0,
        //         'hours' => $pilot->flying_hours ?? 0,
        //         'rating' => 'PPL',
        //         'location' => 'Unknown',
        //         'last_flight' => 'Never',
        //         'progress' => '0%',
        //     ];
        // });


        // Create dummy data for the pilots
        $pilots = [
            [
                'id' => 1,
                'name' => 'John Doe',
                'callsign' => '9WVA003',
                'email' => 'john.doe1@example.com',
                'rank' => 'Cadet | 0-15 Hrs',
                'rank_color' => 'bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-full px-1 py-1 text-center text-sm',
                'status' => '1',
                'flights' => 10,
                'hours' => 100,
                'last_flights' => ['VIDP-VABB', 'VABB-VIDP', 'VIDP-VOBL', 'VOBL-VIDP'],
            ],
            [
                'id' => 2,
                'name' => 'Jane Doe',
                'callsign' => '9WVA004',
                'email' => 'jane.doe1@example.com',
                'rank' => 'Jr. First Officer | 15-30 Hrs',
                'rank_color' => 'bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full px-1 py-1 text-center text-sm',
                'status' => '0',
                'flights' => 10,
                'hours' => 100,
                'last_flights' => ['VIDP-VABB', 'VABB-VIDP', 'VIDP-VOBL', 'VOBL-VIDP'],
            ],
            [
                'id' => 3,
                'name' => 'Jim Doe',
                'callsign' => '9WVA005',
                'email' => 'jim.doe1@example.com',
                'rank' => 'First Officer | 30-45 Hrs',
                'rank_color' => 'bg-gradient-to-r from-red-500 to-red-600 text-white rounded-full px-1 py-1 text-center text-sm',
                'status' => '1',
                'flights' => 10,
                'hours' => 100,
                'last_flights' => ['VIDP-VABB', 'VABB-VIDP', 'VIDP-VOBL', 'VOBL-VIDP'],
            ],
            [
                'id' => 1,
                'name' => 'Jill Doe',
                'callsign' => '9WVA006',
                'email' => 'jill.doe1@example.com',
                'rank' => 'Captain | 45-60 Hrs',
                'rank_color' => 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full px-1 py-1 text-center text-sm',
                'status' => '1',
                'flights' => 10,
                'hours' => 100,
                'last_flights' => ['VIDP-VABB', 'VABB-VIDP', 'VIDP-VOBL', 'VOBL-VIDP'],
            ],
            [
                'id' => 4,
                'name' => 'Jill Doe',
                'callsign' => '9WVA007',
                'email' => 'jill.doe2@example.com',
                'rank' => 'Commander | 60+ Hrs',
                'rank_color' => 'bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-full px-1 py-1 text-center text-sm',
                'status' => '0',
                'flights' => 10,
                'hours' => 100,
                'last_flights' => ['VIDP-VABB', 'VABB-VIDP', 'VIDP-VOBL', 'VOBL-VIDP'],
            ],
        ];
        return response()->json($pilots);
    }
}