<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Route extends Model
{
    use SoftDeletes;

    protected $table = 'routes';
    protected $fillable = ['flight_number', 'fleet_id', 'origin', 'destination', 'distance', 'flight_time', 'min_rank_id', 'status'];

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleet_id');
    }
}
