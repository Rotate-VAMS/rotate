<?php

namespace App\Models\Extended;

use App\Models\CustomFieldValues;
use App\Models\CustomFieldConfiguration;
use Illuminate\Database\Eloquent\Model;

class _CustomFieldValues extends Model
{
    const SOURCE_TYPE_PILOTS = 1;

    const SOURCE_TYPE_PIREPS = 2;
    
    const SOURCE_TYPE_EVENTS = 3;

    public static function getAllCustomFieldValues($source_type, $source_id)
    {
        $customFieldValues = CustomFieldValues::where('source_type', $source_type)->where('source_id', $source_id)->get();
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
}
