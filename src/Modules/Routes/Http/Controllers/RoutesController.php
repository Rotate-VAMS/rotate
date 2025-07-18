<?php

namespace Modules\Routes\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoutesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Breadcrumbs
        $breadcrumbs = [
            [
                'title' => 'Routes'
            ]
        ];
        return Inertia::render('Routes/Pages/Routes', ['breadcrumbs' => $breadcrumbs]);
    }

    public function jxFetchRoutes()
    {
        // Dumy data
        $routes = [
            [
                'flight_number' => '9W101',
                'route' => 'VIDP-KJFK',
                'aircraft' => ['Jet Airways B77W', 'Jet Airways B738'],
                'distance' => 2342,
                'flight_time' => '12:30',
                'type' => 'Long Haul', 
                'minimum_rank' => 'Captain',
                'status' => 'Active',
            ],
            [
                'flight_number' => '9W102',
                'route' => 'VABB-VIDP',
                'aircraft' => ['Jet Airways B738', 'Jet Airways B738'],
                'distance' => 344,
                'flight_time' => '2:10',
                'type' => 'Short Haul', 
                'minimum_rank' => 'First Officer',
                'status' => 'Inactive',
            ],
        ];

        return response()->json([
            'message' => 'Routes fetched successfully',
            'routes' => $routes
        ]);
    }

    public function jxCreateEditRoutes(Request $request)
    {
        // Validate the request
        $request->validate([
            'flight_number' => 'required|string|max:255',
            ''
        ]);
    }

    public function jxDeleteRoutes(Request $request)
    {
        // Validate the request
        $request->validate([
            'flight_number' => 'required|string|max:255',
        ]);
    }
}
