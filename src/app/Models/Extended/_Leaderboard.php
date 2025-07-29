<?php

namespace App\Models\Extended;

use App\Models\Leaderboard;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class _Leaderboard extends Model
{   
    // FLIGHT OPERATIONS

    const EVENT_FILE_PIREP = 'pirep';
    
    const EVENT_FLIGHT_SHORT_HAUL = 'flight_short_haul';

    const EVENT_FLIGHT_LONG_HAUL = 'flight_long_haul';

    const EVENT_FLIGHT_ULTRA_LONG_HAUL = 'flight_ultra_long_haul';

    // MILESTONES

    const EVENT_MILESTONE_FIRST_PIREP = 'milestone_first_pirep';

    const EVENT_MILESTONE_HUNDERED_PIREPS = 'milestone_hundred_pireps';

    const EVENT_MILESTONE_FIVE_HUNDRED_PIREPS = 'milestone_five_hundred_pireps';

    const EVENT_MILESTONE_ONE_THOUSAND_PIREPS = 'milestone_one_thousand_pireps'; 
    
    public static function getLeaderboardEvents()
    {
        return [
            self::EVENT_FILE_PIREP,
            self::EVENT_FLIGHT_SHORT_HAUL,
            self::EVENT_FLIGHT_LONG_HAUL,
            self::EVENT_FLIGHT_ULTRA_LONG_HAUL,
            self::EVENT_MILESTONE_FIRST_PIREP,
            self::EVENT_MILESTONE_HUNDERED_PIREPS,
            self::EVENT_MILESTONE_FIVE_HUNDRED_PIREPS,
            self::EVENT_MILESTONE_ONE_THOUSAND_PIREPS,
        ];
    }

    public static function getLeaderboardEventsPoints()
    {
        $leaderboardEvents = self::all();
        $flightOperations = [self::EVENT_FILE_PIREP, self::EVENT_FLIGHT_SHORT_HAUL, self::EVENT_FLIGHT_LONG_HAUL, self::EVENT_FLIGHT_ULTRA_LONG_HAUL];
        $milestones = [self::EVENT_MILESTONE_FIRST_PIREP, self::EVENT_MILESTONE_HUNDERED_PIREPS, self::EVENT_MILESTONE_FIVE_HUNDRED_PIREPS, self::EVENT_MILESTONE_ONE_THOUSAND_PIREPS];
        
        foreach ($leaderboardEvents as $leaderboardEvent) {
            if (in_array($leaderboardEvent->leaderboard_event_name, $flightOperations)) {
                $leaderboardEvent->group = 'Flight Operations';
            } else if (in_array($leaderboardEvent->leaderboard_event_name, $milestones)) {
                $leaderboardEvent->group = 'Milestones';
            }
        }
        
        return $leaderboardEvents;
    }

    public static function updateLeaderboardEvent($event_name, $points)
    {
        $leaderboardEvent = Leaderboard::where('leaderboard_event_name', $event_name)->first();
        if (!$leaderboardEvent) {
            return ['error' => 'Leaderboard event not found'];
        }
        $leaderboardEvent->points = $points;
        if (! $leaderboardEvent->save()) {
            return ['error' => 'Failed to update leaderboard event'];
        }

        return ['leaderboard_event' => $leaderboardEvent, 'success' => true];
    }

    public static function logLeaderboardEvent($user_id, $leaderboard_event)
    {
        $leaderboardEvent = Leaderboard::where('leaderboard_event_name', $leaderboard_event)->first();
        if (!$leaderboardEvent) {
            return ['error' => 'Leaderboard event not found'];
        }

        // Log leaderboard event
        DB::table('leaderboard_points_log')->insert([
            'user_id' => $user_id,
            'leaderboard_event_id' => $leaderboardEvent->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Fetch total user points
        $totalUserPoints = DB::table('leaderboard_points_log')
            ->leftJoin('leaderboard_points_configuration as lpc', 'leaderboard_points_log.leaderboard_event_id', '=', 'lpc.id')
            ->select(DB::raw('SUM(lpc.points) as points'))
            ->where('user_id', $user_id)
            ->first();

        // Update user points
        $user = User::find($user_id);
        $user->points = $totalUserPoints->points ?? 0;

        if (! $user->save()) {
            return ['error' => 'Failed to update user points'];
        }

        return ['success' => true];
    }
}