<?php

namespace App\Models;

use App\Models\Extended\_FlightType;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightType extends _FlightType
{
    use SoftDeletes;

    protected $table = 'flight_types';
    protected $fillable = ['flight_type', 'multiplier'];

    public function pireps()
    {
        return $this->hasMany(Pirep::class, 'flight_type_id');
    }
}