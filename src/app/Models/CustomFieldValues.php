<?php

namespace App\Models;

use App\Models\Extended\_CustomFieldValues;

class CustomFieldValues extends _CustomFieldValues
{
    protected $table = 'custom_field_values';

    protected $fillable = [
        'field_id',
        'source_type',
        'source_id',
        'value',
    ];

    public function customFieldConfiguration()
    {
        return $this->belongsTo(CustomFieldConfiguration::class, 'field_id', 'id');
    }
}
