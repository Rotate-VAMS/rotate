<?php

namespace App\Models;

use App\Models\Extended\_EventAttendance;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class EventAttendance extends _EventAttendance
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'event_attendance';
    protected $fillable = ['event_id', 'user_id'];

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}
