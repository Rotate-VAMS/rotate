<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FlightType extends Model
{
    use SoftDeletes;

    protected $table = 'flight_types';
    protected $fillable = ['flight_type', 'multiplier'];

    public function pireps()
    {
        return $this->hasMany(Pirep::class, 'flight_type_id');
    }
}