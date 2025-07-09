<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pirep extends Model
{
    use SoftDeletes;

    protected $table = 'pireps';
    protected $fillable = ['user_id', 'route_id', 'flight_time', 'flight_type_id', 'computed_flight_time'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
