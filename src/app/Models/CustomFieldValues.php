<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValues extends Model
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
