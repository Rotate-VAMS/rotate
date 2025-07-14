<?php

namespace App\Models\Extended;

use App\Models\EventAttendance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class _EventAttendance extends Model
{
    use SoftDeletes;

    public static function deleteEventAttendance($eventId)
    {
        $eventAttendance = EventAttendance::where('event_id', $eventId)->get();
        if ($eventAttendance->isEmpty()) {
            return ['error' => 'Event attendance not found'];
        }

        foreach ($eventAttendance as $attendance) {
            $attendance->delete();
        }
    }
}
