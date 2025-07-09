<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldOptions extends Model
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
