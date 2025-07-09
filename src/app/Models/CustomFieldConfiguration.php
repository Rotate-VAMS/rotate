<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldConfiguration extends Model
{
    protected $table = 'custom_field_configuration';

    protected $fillable = [
        'field_name',
        'field_key',
        'field_description',
        'data_type',
        'aggregation_type',
        'source_type',
        'is_required',
        'is_active',
    ];

    public function customFieldValues()
    {   
        return $this->hasMany(CustomFieldValues::class, 'field_id', 'id');
    }
}
