<?php

namespace App\Models;

use App\Models\Extended\_Route;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Route extends _Route
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'routes';
    protected $fillable = ['flight_number', 'fleet_id', 'origin', 'destination', 'distance', 'flight_time', 'min_rank_id', 'status', 'tenant_id'];

    public function fleet()
    {
        return $this->belongsTo(Fleet::class, 'fleet_id');
    }
}
