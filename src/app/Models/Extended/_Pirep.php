<?php

namespace App\Models\Extended;

use App\Models\FlightType;
use App\Models\Pirep;
use App\Models\CustomFieldValues;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

        return ['success' => $mode === 'create' ? 'Pirep created successfully' : 'Pirep updated successfully'];
    }

}
