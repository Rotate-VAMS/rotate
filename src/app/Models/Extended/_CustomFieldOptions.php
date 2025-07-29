<?php

namespace App\Models\Extended;

use App\Models\CustomFieldConfiguration;
use App\Models\CustomFieldOptions;
use App\Models\Route;
use App\Models\User;
use App\Models\Event;
use App\Models\Fleet;
use App\Models\Pirep;
use App\Models\Rank;
use Illuminate\Database\Eloquent\Model;

class _CustomFieldOptions extends Model
{
    const CUSTOM_VALUES_AS_ROUTE = 1;

    const CUSTOM_VALUES_AS_USERS = 2;

    const CUSTOM_VALUES_AS_EVENTS = 3;

    const CUSTOM_VALUES_AS_FLEET = 4;

    const CUSTOM_VALUES_AS_PIREPS = 5;

    const CUSTOM_VALUES_AS_RANKS = 6;

    const CUSTOM_VALUES_AS_CUSTOM_INPUT = 7;

    public static function createCustomFieldOption($data, $mode)
    {
        if ($mode === 'create') {
            foreach ($data['options'] as $option) {
                $customFieldOption = new CustomFieldOptions();
                $customFieldOption->field_id = $data['field_id'];
                $customFieldOption->value = $option;
                $customFieldOption->label = $option;
                $customFieldOption->created_at = now();
                $customFieldOption->updated_at = now();
                
                if (!$customFieldOption->save()) {
                    return false;
                }
            }
        } else {
            $customFieldOption = CustomFieldOptions::find($data['id']);
            if (!$customFieldOption) {
                return false;
            }
            foreach ($data['options'] as $option) {
                $customFieldOption = CustomFieldOptions::find($option['id']);
                if (!$customFieldOption) {
                    return false;
                }
                $customFieldOption->value = $option;
                $customFieldOption->label = $option;
                $customFieldOption->created_at = now();
                $customFieldOption->updated_at = now();
                if (!$customFieldOption->save()) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function fetchCustomFieldOptions($field_id)
    {
        $cfc = CustomFieldConfiguration::find($field_id);
        if (!$cfc) {
            return false;
        }
        if ($cfc->data_type != CustomFieldConfiguration::DATA_TYPE_DROPDOWN) {
            return ['hasErrors' => true, 'errors' => 'Custom field is not a dropdown'];
        }

        switch ($cfc->dropdown_value_type) {
            case self::CUSTOM_VALUES_AS_ROUTE:
                $fetchedRoutes = Route::all();
                foreach ($fetchedRoutes as $route) {
                    $customFieldOptions[] = $route->origin . '-' . $route->destination;
                }
                break;
            case self::CUSTOM_VALUES_AS_USERS:
                $fetchedUsers = User::all();
                foreach ($fetchedUsers as $user) {
                    $customFieldOptions[] = $user->name . ' (' . $user->email . ')';
                }
                break;
            case self::CUSTOM_VALUES_AS_EVENTS:
                $fetchedEvents = Event::all();
                foreach ($fetchedEvents as $event) {
                    $customFieldOptions[] = $event->name . ' (' . $event->start_date . ' - ' . $event->end_date . ')';
                }
                break;
            case self::CUSTOM_VALUES_AS_FLEET:
                $fetchedFleets = Fleet::all();
                foreach ($fetchedFleets as $fleet) {
                    $customFieldOptions[] = $fleet->livery . ' - ' . $fleet->aircraft;
                }
                break;
            case self::CUSTOM_VALUES_AS_PIREPS:
                $fetchedPireps = Pirep::all();
                foreach ($fetchedPireps as $pirep) {
                    $route = Route::find($pirep->route_id);
                    $customFieldOptions[] = $pirep->id . ' - ' . $pirep->user->name . ' - (' . $route->origin . '-' . $route->destination . ')';
                }
                break;
            case self::CUSTOM_VALUES_AS_RANKS:
                $fetchedRanks = Rank::all();
                foreach ($fetchedRanks as $rank) {
                    $customFieldOptions[] = $rank->name . ' (' . $rank->min_hours . ' hours)';
                }
                break;
            case self::CUSTOM_VALUES_AS_CUSTOM_INPUT:
                $customFieldOptions = CustomFieldOptions::where('field_id', $field_id)->get()->pluck('label');
                break;
            default:
                return ['hasErrors' => true, 'errors' => 'Invalid dropdown value type'];
        }

        return $customFieldOptions;
    }
}
