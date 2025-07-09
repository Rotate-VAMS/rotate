<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $analytics = [
            ['title' => 'Total Pilots', 'value' => 247, 'growth' => 12, 'type' => 'pilots', 'type' => 'pilots'],
            ['title' => 'Active Routes', 'value' => 1456, 'growth' => 23, 'type' => 'routes', 'type' => 'routes'],
            ['title' => 'Monthly Flights', 'value' => 892, 'growth' => 18, 'type' => 'flights', 'type' => 'flights'],
            ['title' => 'Upcoming Events', 'value' => 3, 'growth' => null, 'type' => 'events', 'type' => 'events'],
        ];

        $recentActivities = [
            ['id' => 1, 'initials' => 'JS', 'name' => 'John Smith', 'route' => 'KJFK → EGLL', 'aircraft' => 'B777-300ER', 'status' => 'completed', 'time_ago' => '2h ago'],
            ['id' => 2, 'initials' => 'SJ', 'name' => 'Sarah Johnson', 'route' => 'KLAX → RJTT', 'aircraft' => 'A350-900', 'status' => 'in-progress', 'time_ago' => '4h ago'],
            ['id' => 3, 'initials' => 'MW', 'name' => 'Mike Wilson', 'route' => 'EDDF → OMDB', 'aircraft' => 'A380-800', 'status' => 'completed', 'time_ago' => '6h ago'],
        ];

        $events = [
            ['id' => 1, 'name' => 'Atlantic Crossing Event', 'date' => 'Dec 15, 2024', 'participants' => 45],
            ['id' => 2, 'name' => 'Holiday Charter Flights', 'date' => 'Dec 20, 2024', 'participants' => 23],
            ['id' => 3, 'name' => 'New Year Fly-In', 'date' => 'Jan 1, 2025', 'participants' => 67],
        ];

        $quickLinks = [
            ['label' => 'Browse Routes', 'url' => '/routes', 'icon' => 'RouteIcon'],
            ['label' => 'Airport Charts', 'url' => '/charts', 'icon' => 'MapPinIcon'],
            ['label' => 'Pilot Management', 'url' => '/users', 'icon' => 'UsersIcon'],
            ['label' => 'Settings', 'url' => '/settings', 'icon' => 'SettingsIcon'],
        ];

        return Inertia::render('Dashboard/Pages/DashboardView', [
            'analytics' => $analytics,
            'recentActivities' => $recentActivities,
            'upcomingEvents' => $events,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('dashboard::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('dashboard::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
