<?php

namespace Modules\Dashboard\Http\Controllers;

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
        $events = Event::where('deleted_at', null)->where('event_date_time', '>', time())->orderBy('event_date_time', 'asc')->take(5)->get();
        foreach ($events as $event) {
            $event->participants = EventAttendance::where('event_id', $event->id)->count() ?? 0;
        }

        $upcomingRank = Rank::whereNot('id', $user->rank_id)->orderBy('id', 'asc')->first();
        if (isset($upcomingRank)) {
            $upcomingRank->caption = 'Coming up in ' . ($upcomingRank->min_hours - (User::find($user->id)->flying_hours % 60)) . ' hours';
        } else {
            $upcomingRank = (object) [
                'name' => 'None',
                'caption' => 'None',
            ];
        }

        $analytics = [
            ['title' => 'Your Total Flights', 'value' => Pirep::where('user_id', $user->id)->count(), 'type' => 'flights'],
            ['title' => 'Your Total Routes', 'value' => Route::count(), 'type' => 'routes'],
            ['title' => 'Upcoming Rank', 'value' => $upcomingRank->name, 'caption' => $upcomingRank->caption, 'type' => 'ranks'],
            ['title' => 'Upcoming Events', 'value' => Event::where('event_date_time', '>', now())->count(), 'type' => 'events'],
        ];

        return Inertia::render('Dashboard/Pages/DashboardView', [
            'analytics' => $analytics,
            'recentActivities' => $pireps,
            'upcomingEvents' => $events,
        ]);
    }
}
