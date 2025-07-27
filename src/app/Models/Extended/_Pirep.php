<?php

namespace App\Models\Extended;

use App\Models\FlightType;
use App\Models\Pirep;
use App\Models\CustomFieldValues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Leaderboard;
use App\Models\SystemSettings;

class _Pirep extends Model
{
    public static function createEditPirep($data, $mode)
    {
        if ($mode === 'create') {
            $pirep = new Pirep();
        } else {
            $pirep = Pirep::find($data['id']);
        }
        $flightTime = (int)$data['flight_time_hours'] * 60 + (int)$data['flight_time_minutes'];
        $pirep->user_id = Auth::user()->id;
        $pirep->route_id = $data['route_id'];
        $pirep->flight_time = $flightTime;
        $pirep->flight_type_id = $data['flight_type_id'];

        // Compute the computed fligth time
        $pirep->computed_flight_time = $flightTime * FlightType::find($pirep->flight_type_id)->multiplier;

        if (!$pirep->save()) {
            return ['error' => 'Failed to save pirep'];
        }

        if (isset($data['customData'])) {
            foreach ($data['customData'] as $field_key => $value) {
                CustomFieldValues::createCustomFieldValue(CustomFieldValues::SOURCE_TYPE_PIREPS, $pirep->id, $field_key, $value);
            }
        }

        if (SystemSettings::getSystemSetting(SystemSettings::LEADERBOARD_POINTS_CONFIGURATION)->value === SystemSettings::SETTING_ENABLED && $mode === 'create') {
            $pirepCount = Pirep::where('user_id', $pirep->user_id)->count();

            Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_FILE_PIREP);
            
            // Milestones
            if ($pirepCount === 1) {
                Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_MILESTONE_FIRST_PIREP);
            } else if ($pirepCount === 100) {
                Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_MILESTONE_HUNDERED_PIREPS);
            } else if ($pirepCount === 500) {
                Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_MILESTONE_FIVE_HUNDRED_PIREPS);
            } else if ($pirepCount === 1000) {
                Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_MILESTONE_ONE_THOUSAND_PIREPS);
            } else {
                // Do nothing
            }

            // Flight time
            if (0 < $pirep->flight_time && $pirep->flight_time < 240) {
                Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_FLIGHT_SHORT_HAUL);
            } else if ($pirep->flight_time >= 240 && $pirep->flight_time < 480) {
                Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_FLIGHT_LONG_HAUL);
            } else if ($pirep->flight_time >= 480) {
                Leaderboard::logLeaderboardEvent($pirep->user_id, Leaderboard::EVENT_FLIGHT_ULTRA_LONG_HAUL);
            } else {
                // Do nothing
            }
        }

        return ['success' => $mode === 'create' ? 'Pirep created successfully' : 'Pirep updated successfully'];
    }

}
