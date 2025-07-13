<?php

namespace App\Models\Extended;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomFieldConfiguration;
use App\Models\CustomFieldOptions;
use App\Models\CustomFieldValues;
use Illuminate\Support\Str;

class _CustomFieldConfiguration extends Model
{
    const DATA_TYPE_TEXT = 1;
    
    const DATA_TYPE_INTEGER = 2;
    
    const DATA_TYPE_FLOAT = 3;
    
    const DATA_TYPE_BOOLEAN = 4;
    
    const DATA_TYPE_DATE = 5;

    const DATA_TYPE_DROPDOWN = 6;

    const AGGREGATION_TYPE_SUM = 1;
    
    const AGGREGATION_TYPE_AVERAGE = 2;
    
    const AGGREGATION_TYPE_COUNT = 3;
    
    const AGGREGATION_TYPE_MIN = 4;
    
    const AGGREGATION_TYPE_MAX = 5;
    
    const SOURCE_TYPE_PILOTS = 1;
    
    const SOURCE_TYPE_PIREPS = 2;
    
    const SOURCE_TYPE_EVENTS = 3;

    public static function createCustomFieldConfiguration($data, $mode)
    {
        if ($mode === 'edit') {
            $customFieldConfiguration = CustomFieldConfiguration::find($data['id']);
            if (!$customFieldConfiguration) {
                return false;
            }
        } else {
            $customFieldConfiguration = new CustomFieldConfiguration();
        }
        $customFieldConfiguration->field_name = $data['field_name'];
        $customFieldConfiguration->field_description = $data['field_description'];
        $customFieldConfiguration->data_type = $data['data_type'];
        $customFieldConfiguration->aggregation_type = $data['aggregation_type'];
        $customFieldConfiguration->source_type = $data['source_type'];
        $customFieldConfiguration->is_required = $data['is_required'];
        $customFieldConfiguration->is_active = true;
        $customFieldConfiguration->field_key = Str::slug($data['field_name']);
        $customFieldConfiguration->created_at = now();
        $customFieldConfiguration->updated_at = now();
        $customFieldConfiguration->save();
        return $customFieldConfiguration;
    }

    public static function getUserCustomFields()
    {
        $customFields = CustomFieldConfiguration::where('source_type', self::SOURCE_TYPE_PILOTS)->get();

        // If custom field is a dropdown, get the options
        foreach ($customFields as $customField) {
            if ($customField->data_type === self::DATA_TYPE_DROPDOWN) {
                $customField->options = CustomFieldOptions::where('field_id', $customField->id)->get();
            }
        }

        return $customFields;
    }

    public static function deleteCustomFieldConfiguration($id)
    {
        // Check for any field options and delete them first
        $customFieldOptions = CustomFieldOptions::where('field_id', $id)->get();
        foreach ($customFieldOptions as $customFieldOption) {
            if (!$customFieldOption->delete()) {
                return ['error' => 'Failed to delete custom field option'];
            }
        }

        // Check for any field values and delete them first
        $customFieldValues = CustomFieldValues::where('field_id', $id)->get();
        foreach ($customFieldValues as $customFieldValue) {
            if (!$customFieldValue->delete()) {
                return ['error' => 'Failed to delete custom field value'];
            }
        }
        // Delete the custom field configuration
        $customFieldConfiguration = CustomFieldConfiguration::find($id);
        if (!$customFieldConfiguration->delete()) {
            return ['error' => 'Failed to delete custom field configuration'];
        }

        return true;
    }
}
