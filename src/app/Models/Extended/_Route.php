<?php

namespace App\Models\Extended;

use App\Models\Fleet;
use Illuminate\Database\Eloquent\Model;
use App\Models\Route;
use App\Helpers\RotateConstants;
use App\Helpers\RotateAirportHelper;
use App\Models\CustomFieldValues;
class _Route extends Model
{
    const ROUTE_STATUS_ACTIVE = 1;
    
    const ROUTE_STATUS_INACTIVE = 0;

    public static function createEditRoute($data, $mode)
    {
        if ($mode === 'create') {
            $route = new Route();
            $route->created_at = now();
        } else {
            $route = Route::find($data['id']);
            if (!$route) {
                return ['error' => 'Route not found'];
            }
        }
        $route->flight_number = $data['flight_number'];
        $route->origin = $data['origin_icao'];
        $route->destination = $data['destination_icao'];
        $route->fleet_ids = json_encode($data['fleet_ids']);

        // Check and update rank as per user's choice
        if ($data['use_aircraft_rank']) {
            $fleet = Fleet::whereIn('id', $data['fleet_ids'])->get();
            $rank_ids = $fleet->pluck('minimum_rank')->unique();
            if ($rank_ids->count() > RotateConstants::CONSTANT_FOR_ONE) {
                return ['error' => 'All fleets must have the same rank'];
            }
            $route->min_rank_id = $rank_ids->first();
        } else {
            $route->min_rank_id = $data['rank_id'] ?? null;
        }

        $route->distance = RotateAirportHelper::distanceBetweenICAOs($data['origin_icao'], $data['destination_icao']) ?? 0;
        $route->flight_time = ($route->distance / 450) * 60;
        $route->updated_at = now();

        if (!$route->save()) {
            return ['error' => 'Failed to save route'];
        }

        // Handle and save custom data
        if (isset($data['customData'])) {
            foreach ($data['customData'] as $field_key => $value) {
                CustomFieldValues::createCustomFieldValue(CustomFieldValues::SOURCE_TYPE_ROUTES, $route->id, $field_key, $value);
            }
        }

        return ['success' => 'Route saved successfully'];
    }
}
