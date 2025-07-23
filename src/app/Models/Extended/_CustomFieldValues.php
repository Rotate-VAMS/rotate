<?php

namespace App\Models\Extended;

use App\Models\CustomFieldValues;
use App\Models\CustomFieldConfiguration;
use App\Helpers\RotateConstants;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class _CustomFieldValues extends Model
{
    const SOURCE_TYPE_PILOTS = 1;

    const SOURCE_TYPE_PIREPS = 2;
    
    const SOURCE_TYPE_EVENTS = 3;

    const SOURCE_TYPE_ROUTES = 4;

    public static function getAllCustomFieldValues($source_type, $source_id)
    {
        $customFieldValues = DB::table('custom_field_values')
            ->join('custom_field_configuration', 'custom_field_values.field_id', '=', 'custom_field_configuration.id')
            ->select('custom_field_values.*', 'custom_field_configuration.data_type', 'custom_field_configuration.field_name', 'custom_field_configuration.field_description', 'custom_field_configuration.field_key')
            ->where('custom_field_values.source_type', $source_type)
            ->where('custom_field_values.source_id', $source_id)
            ->get();

        foreach ($customFieldValues as $customFieldValue) {
            switch ($customFieldValue->data_type) {
                case CustomFieldConfiguration::DATA_TYPE_TEXT:
                    $customFieldValue->value_display = $customFieldValue->value;
                    break;
                case CustomFieldConfiguration::DATA_TYPE_INTEGER:
                    $customFieldValue->value_display = $customFieldValue->value;
                    break;
                case CustomFieldConfiguration::DATA_TYPE_FLOAT:
                    $customFieldValue->value_display = $customFieldValue->value;
                    break;
                case CustomFieldConfiguration::DATA_TYPE_BOOLEAN:
                    $customFieldValue->value = $customFieldValue->value == RotateConstants::CONSTANT_FOR_ONE ? true : false;
                    $customFieldValue->value_display = $customFieldValue->value == RotateConstants::CONSTANT_FOR_ONE ? 'Yes' : 'No';
                    break;
                case CustomFieldConfiguration::DATA_TYPE_DROPDOWN:
                    $customFieldValue->value_display = $customFieldValue->value;
                    break;
                case CustomFieldConfiguration::DATA_TYPE_DATE:
                    $customFieldValue->value_display = $customFieldValue->value;
                    break;
                default:
                    break;
            }
        }

        return $customFieldValues;
    }

    public static function getCustomFieldValue($source_type, $source_id, $field_id)
    {
        $customFieldValue = CustomFieldValues::where('source_type', $source_type)->where('source_id', $source_id)->where('field_id', $field_id)->get();
        return $customFieldValue;
    }

    public static function createCustomFieldValue($source_type, $source_id, $field_key, $value)
    {
        // Find the field_key
        $field = CustomFieldConfiguration::where('field_key', $field_key)->first();
        if (!$field) {
            return ['error' => 'Field not found'];
        }

        // Check if custom data exists for matching details provided
        $customData = CustomFieldValues::where('source_type', $source_type)->where('source_id', $source_id)->where('field_id', $field->id)->first();
        if ($customData) {
            $customData->value = $value;
            $customData->updated_at = now();
            $customData->save();
        } else {
            CustomFieldValues::create([
                'field_id' => $field->id,
                'source_type' => $source_type,
                'source_id' => $source_id,
                'value' => $value,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        return true;
    }

    public static function deleteCustomFieldValues($source_type, $source_id)
    {
        $customFieldValues = CustomFieldValues::where('source_type', $source_type)->where('source_id', $source_id)->get();
        if ($customFieldValues->isEmpty()) {
            return true;
        }
        foreach ($customFieldValues as $customFieldValue) {
            if (!$customFieldValue->delete()) {
                return false;
            }
        }
        return true;
    }
}
