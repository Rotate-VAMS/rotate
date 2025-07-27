<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Leaderboard;
use App\Models\SystemSettings;
use App\Helpers\RotateConstants;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function jxGetLeaderboardSettings()
    {
        $leaderboardSettings = SystemSettings::getSystemSetting(SystemSettings::LEADERBOARD_POINTS_CONFIGURATION);
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

        return response()->json([
            'hasErrors' => false,
            'message' => 'Leaderboard settings updated successfully',
            'data' => $leaderboardSettings->value
        ]);
    }

    public function jxGetLeaderboardEvents()
    {
        $leaderboardEvents = Leaderboard::getLeaderboardEventsPoints();
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

        return response()->json([
            'hasErrors' => false,
            'message' => 'Leaderboard event updated successfully',
            'data' => $leaderboardEvent
        ]);
    }
}