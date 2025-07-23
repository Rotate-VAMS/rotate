<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Pirep;
use App\Models\Route;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pireps = DB::table('pireps')
            ->leftJoin('routes', 'pireps.route_id', '=', 'routes.id')
            ->leftJoin('flight_types', 'pireps.flight_type_id', '=', 'flight_types.id')
            ->leftJoin('users', 'pireps.user_id', '=', 'users.id')
            ->select('pireps.*', 'routes.flight_number', 'routes.origin', 'routes.destination', 'routes.distance', 'flight_types.flight_type as flight_type_name', 'users.name as pilot_name')
            ->where('pireps.deleted_at', null)
            ->orderBy('pireps.created_at', 'desc')
            ->take(5)
            ->get();
        foreach ($pireps as $pirep) {
            $pirep->route = $pirep->origin . ' - ' . $pirep->destination;
            $pirep->time_ago = Carbon::parse($pirep->created_at)->diffForHumans();
        }

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
            ['title' => 'Total Pilots', 'value' => User::count(), 'type' => 'pilots', 'type' => 'pilots'],
            ['title' => 'Active Routes', 'value' => Route::where('status', Route::ROUTE_STATUS_ACTIVE)->count(), 'type' => 'routes', 'type' => 'routes'],
            ['title' => 'Monthly Flights', 'value' => Pirep::where('created_at', '>=', now()->subMonth())->count(), 'type' => 'flights', 'type' => 'flights'],
            ['title' => 'Upcoming Events', 'value' => Event::where('event_date_time', '>', now())->count(), 'type' => 'events', 'type' => 'events'],
        ];

        return Inertia::render('Dashboard/Pages/DashboardView', [
            'analytics' => $analytics,
            'recentActivities' => $pireps,
            'upcomingEvents' => $events,
            'quickLinks' => $quickLinks,
        ]);
    }
}
