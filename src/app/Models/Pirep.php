<?php

namespace App\Models;

use App\Models\Extended\_Pirep;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pirep extends _Pirep
{
    use SoftDeletes;

    protected $table = 'pireps';
    protected $fillable = ['user_id', 'route_id', 'flight_time', 'flight_type_id', 'computed_flight_time'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
