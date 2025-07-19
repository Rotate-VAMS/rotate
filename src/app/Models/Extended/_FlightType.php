<?php

namespace App\Models\Extended;

use Illuminate\Database\Eloquent\Model;
use App\Models\FlightType;

class _FlightType extends Model
{

    public static function createFlightType($data, $mode)
    {
        if ($mode == 'create') {
            $flightType = new FlightType();
            $flightType->created_at = now();
        } else {
            $flightType = FlightType::find($data['id']);
            if (!$flightType) {
                return false;
            }
        }
        $flightType->flight_type = $data['flight_type'];
        $flightType->multiplier = (float)$data['multiplier'];
        $flightType->updated_at = now();
        if (!$flightType->save()) {
            return false;
        }
        return $flightType;
    }

}
