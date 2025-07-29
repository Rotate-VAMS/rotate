<?php

namespace App\Models;

use App\Models\Extended\_Event;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Event extends _Event
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'events';
    protected $fillable = ['event_name', 'event_description', 'event_date_time', 'origin', 'destination', 'aircraft'];

    public function eventAttendance()
    {
        return $this->hasMany(EventAttendance::class, 'event_id');
    }
}
