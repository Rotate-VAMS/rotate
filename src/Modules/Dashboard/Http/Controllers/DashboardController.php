<?php

namespace Modules\Dashboard\Http\Controllers;

use App\Helpers\RotateAirportHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Pirep;
use App\Models\Route;
use App\Models\Rank;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\Diff\Diff;
use Modules\Integration\Http\Controllers\LeaderboardController;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $pireps = DB::table('pireps')
            ->leftJoin('routes', 'pireps.route_id', '=', 'routes.id')
            ->leftJoin('flight_types', 'pireps.flight_type_id', '=', 'flight_types.id')
            ->leftJoin('users', 'pireps.user_id', '=', 'users.id')
            ->leftJoin('events', 'pireps.event_id', '=', 'events.id')
            ->select('pireps.*', 'routes.flight_number', 'routes.origin', 'routes.destination', 'routes.distance', 'flight_types.flight_type as flight_type_name', 'users.name as pilot_name', 'events.event_name', 'events.origin as event_origin', 'events.destination as event_destination')
            ->where('pireps.deleted_at', null)
            ->where('pireps.tenant_id', app('currentTenant')->id)
            ->orderBy('pireps.created_at', 'desc')
            ->take(5)
            ->get();
        foreach ($pireps as $pirep) {
            $pirep->origin = $pirep->origin ?? $pirep->event_origin;
            $pirep->destination = $pirep->destination ?? $pirep->event_destination;
            $pirep->route = $pirep->origin  . ' - ' . $pirep->destination;
            $pirep->distance = $pirep->distance ?? RotateAirportHelper::distanceBetweenICAOs($pirep->origin, $pirep->destination);
            $pirep->time_ago = Carbon::parse($pirep->created_at)->diffForHumans();
            $pirep->event_name = $pirep->event_name ?? 'None';
        }

        // Fetch latest 5 events
        $events = Event::where('deleted_at', null)->where('event_date_time', '>', time())->orderBy('event_date_time', 'asc')->take(5)->get();
        foreach ($events as $event) {
            $event->participants = EventAttendance::where('event_id', $event->id)->count() ?? 0;
        }

        $upcomingRank = Rank::whereNot('id', $user->rank_id)->where('min_hours', '>', $user->flying_hours)->orderBy('id', 'asc')->first();
        if (isset($upcomingRank)) {
            $upcomingRank->caption = 'Coming up in ' . ($upcomingRank->min_hours - (User::find($user->id)->flying_hours % 60)) . ' hours';
        } else {
            $upcomingRank = (object) [
                'name' => 'None',
                'caption' => 'None',
            ];
        }

        $leaderboardController = new LeaderboardController();
        $leaderboard = $leaderboardController->jxGetUserLeaderboardData(new Request(['view' => 'dashboard']));
        $leaderboardData = $leaderboard->getData()->data;

        $analytics = [
            ['title' => 'Your Total Flights', 'value' => Pirep::where('user_id', $user->id)->count(), 'type' => 'flights'],
            ['title' => 'Your Total Routes', 'value' => Route::count(), 'type' => 'routes'],
            ['title' => 'Upcoming Rank', 'value' => $upcomingRank->name, 'caption' => $upcomingRank->caption, 'type' => 'ranks'],
            ['title' => 'Upcoming Events', 'value' => Event::where('event_date_time', '>', time())->where('tenant_id', app('currentTenant')->id)->count(), 'type' => 'events'],
        ];

        return Inertia::render('Dashboard/Pages/DashboardView', [
            'analytics' => $analytics,
            'recentActivities' => $pireps,
            'upcomingEvents' => $events,
            'leaderboard' => $leaderboardData,
        ]);
    }
}
