<?php

namespace App\Models;

use App\Models\Extended\_CustomFieldOptions;

class CustomFieldOptions extends _CustomFieldOptions
{
    protected $table = 'custom_field_options';

    protected $fillable = [
        'field_id',
        'value',
        'label',
    ];

    public function customFieldConfiguration()
    {
        return $this->belongsTo(CustomFieldConfiguration::class, 'field_id', 'id');
    }
}
