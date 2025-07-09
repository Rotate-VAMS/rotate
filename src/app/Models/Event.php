<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $table = 'events';
    protected $fillable = ['event_name', 'event_description', 'event_date_time', 'origin', 'destination', 'aircraft'];

    public function eventAttendance()
    {
        return $this->hasMany(EventAttendance::class, 'event_id');
    }
}
