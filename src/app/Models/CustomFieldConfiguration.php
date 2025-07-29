<?php

namespace App\Models;

use App\Models\Extended\_CustomFieldConfiguration;
use App\Models\Traits\BelongsToTenant;

class CustomFieldConfiguration extends _CustomFieldConfiguration
{
    use BelongsToTenant;

    protected $table = 'custom_field_configuration';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_description',
        'data_type',
        'source_type',
        'is_required',
        'is_active',
    ];

    public function customFieldValues()
    {   
        return $this->hasMany(CustomFieldValues::class, 'field_id', 'id');
    }
}
