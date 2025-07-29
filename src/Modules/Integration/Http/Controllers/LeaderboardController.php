<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Models\SystemSettings;
use App\Helpers\RotateConstants;
use App\Models\Pirep;
use App\Models\User;
use App\Models\Rank;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeaderboardController extends Controller
{
    public function jxGetLeaderboardSettings()
    {
        $cacheKey = 'leaderboard:settings';
        $leaderboardSettings = tenant_cache_remember($cacheKey, RotateConstants::SECONDS_IN_ONE_DAY, function () {
            return SystemSettings::getSystemSetting(SystemSettings::LEADERBOARD_POINTS_CONFIGURATION);
        });

        if (!$leaderboardSettings) {
            return response()->json([
                'hasErrors' => true,
                'message' => 'Leaderboard settings not found'
            ]);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Leaderboard settings fetched successfully',
            'data' => $leaderboardSettings->value
        ]);
    }

    public function jxUpdateLeaderboardSettings(Request $request)
    {
        $leaderboardSettings = SystemSettings::toggleSystemSetting(SystemSettings::LEADERBOARD_POINTS_CONFIGURATION);
        if (isset($leaderboardSettings['error'])) {
            return response()->json([
                'hasErrors' => true,
                'message' => $leaderboardSettings['error']
            ]);
        }

        // Clear leaderboard cache when settings are updated
        tenant_cache_forget('leaderboard:settings');
        tenant_cache_forget('leaderboard:events');
        tenant_cache_forget('leaderboard:user_data');

        return response()->json([
            'hasErrors' => false,
            'message' => 'Leaderboard settings updated successfully',
            'data' => $leaderboardSettings->value
        ]);
    }

    public function jxGetLeaderboardEvents()
    {
        $cacheKey = 'leaderboard:events';
        $leaderboardEvents = tenant_cache_remember($cacheKey, RotateConstants::SECONDS_IN_ONE_DAY, function () {
            return Leaderboard::getLeaderboardEventsPoints();
        });

        return response()->json([
            'hasErrors' => false,
            'message' => 'Leaderboard events fetched successfully',
            'data' => $leaderboardEvents
        ]);
    }

    public function jxUpdateLeaderboardEvent(Request $request)
    {
        $leaderboardEvent = Leaderboard::updateLeaderboardEvent($request->event_name, $request->points);
        if (isset($leaderboardEvent['error'])) {
            return response()->json([
                'hasErrors' => true,
                'message' => $leaderboardEvent['error']
            ]);
        }

        // Clear leaderboard cache when events are updated
        tenant_cache_forget('leaderboard:events');
        tenant_cache_forget('leaderboard:user_data');

        return response()->json([
            'hasErrors' => false,
            'message' => 'Leaderboard event updated successfully',
            'data' => $leaderboardEvent
        ]);
    }

    public function jxGetUserLeaderboardData(Request $request)
    {
        $view = $request->view ?? 'full';
        $cacheKey = "leaderboard:user_data:{$view}";
        
        $leaderboardData = tenant_cache_remember($cacheKey, RotateConstants::SECONDS_IN_ONE_DAY, function () use ($view) {
            $users = User::where('status', User::PILOT_STATUS_ACTIVE)->orderBy('points', 'desc')->get();
            $leaderboardData = [];
            
            foreach ($users as $user) {
                $leaderboardData[$user->id] = [
                    'user_name' => $user->name,
                    'points' => $user->points,
                    'callsign' => $user->callsign,
                    'rank' => $user->rank ? Rank::find($user->rank_id)->name : 'Unknown',
                    'flying_hours' => $user->flying_hours,
                    'total_flights' => Pirep::where('user_id', $user->id)->count(),
                ];
            }

            if ($view === 'dashboard') {
                return collect($leaderboardData)->sortByDesc('points')->take(10)->toArray();
            } else {
                return collect($leaderboardData)->sortByDesc('points')->toArray();
            }
        });

        return response()->json([
            'hasErrors' => false,
            'message' => 'User leaderboard data fetched successfully',
            'data' => $leaderboardData
        ]);
    }

    public function leaderboard()
    {
        $breadcrumbs = [
            [
                'title' => 'Leaderboard'
            ]
        ];
        return Inertia::render('Integration/Pages/Leaderboard', ['breadcrumbs' => $breadcrumbs]);
    }

    /**
     * Clear all leaderboard cache
     */
    public function jxClearLeaderboardCache()
    {
        tenant_cache_forget('leaderboard:settings');
        tenant_cache_forget('leaderboard:events');
        tenant_cache_forget('leaderboard:user_data:dashboard');
        tenant_cache_forget('leaderboard:user_data:full');

        return response()->json([
            'hasErrors' => false,
            'message' => 'Leaderboard cache cleared successfully'
        ]);
    }
}