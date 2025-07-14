<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\EventAttendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $recentActivities = [
            ['id' => 1, 'initials' => 'JS', 'name' => 'John Smith', 'route' => 'KJFK → EGLL', 'aircraft' => 'B777-300ER', 'status' => 'completed', 'time_ago' => '2h ago'],
            ['id' => 2, 'initials' => 'SJ', 'name' => 'Sarah Johnson', 'route' => 'KLAX → RJTT', 'aircraft' => 'A350-900', 'status' => 'in-progress', 'time_ago' => '4h ago'],
            ['id' => 3, 'initials' => 'MW', 'name' => 'Mike Wilson', 'route' => 'EDDF → OMDB', 'aircraft' => 'A380-800', 'status' => 'completed', 'time_ago' => '6h ago'],
        ];

        // Fetch latest 5 events
        $events = Event::orderBy('event_date_time', 'desc')->take(5)->get();
        foreach ($events as $event) {
            $event->event_date_time = Carbon::parse($event->event_date_time)->format('M d, Y h:i A');
            $event->participants = EventAttendance::where('event_id', $event->id)->count() ?? 0;
        }

        $quickLinks = [
            ['label' => 'Browse Routes', 'url' => '/routes', 'icon' => 'RouteIcon'],
            ['label' => 'Airport Charts', 'url' => '/charts', 'icon' => 'MapPinIcon'],
            ['label' => 'Pilot Management', 'url' => '/users', 'icon' => 'UsersIcon'],
            ['label' => 'Settings', 'url' => '/settings', 'icon' => 'SettingsIcon'],
        ];

        $analytics = [
            ['title' => 'Total Pilots', 'value' => 247, 'growth' => 12, 'type' => 'pilots', 'type' => 'pilots'],
            ['title' => 'Active Routes', 'value' => 1456, 'growth' => 23, 'type' => 'routes', 'type' => 'routes'],
            ['title' => 'Monthly Flights', 'value' => 892, 'growth' => 18, 'type' => 'flights', 'type' => 'flights'],
            ['title' => 'Upcoming Events', 'value' => Event::where('event_date_time', '>', now())->count(), 'type' => 'events', 'type' => 'events'],
        ];

        return Inertia::render('Dashboard/Pages/DashboardView', [
            'analytics' => $analytics,
            'recentActivities' => $recentActivities,
            'upcomingEvents' => $events,
            'quickLinks' => $quickLinks,
        ]);
    }
}
