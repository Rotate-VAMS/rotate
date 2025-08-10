<?php

namespace App\Models;

use App\Models\Extended\_Pirep;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use App\Models\Traits\BelongsToTenant;

class Pirep extends _Pirep
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'pireps';
    protected $fillable = ['user_id', 'route_id', 'flight_time', 'flight_type_id', 'computed_flight_time', 'tenant_id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function route()
    {
        return $this->belongsTo(Route::class, 'route_id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id');
    }

    public function flightType()
    {
        return $this->belongsTo(FlightType::class, 'flight_type_id');
    }

    protected static function booted()
    {
        static::created(function ($pirep) {
            $user = User::find(Auth::user()->id);
            $user->flying_hours = Pirep::where('user_id', $user->id)->sum('computed_flight_time');
            $user->save();

            // Update rank if valid
            if ($user->rank_id && isset($user->rank_id)) {
                $user->rank_id = Rank::where('min_hours', '<=', $user->flying_hours/60)->orderBy('min_hours', 'desc')->first()->id;
            } else {
                $user->rank_id = null;
            }
            $user->save();
        });

        static::updated(function ($pirep) {
            $user = User::find(Auth::user()->id);
            $user->flying_hours = Pirep::where('user_id', $user->id)->sum('computed_flight_time');
            $user->save();

            // Update rank if valid
            if ($user->rank_id && isset($user->rank_id)) {
                $user->rank_id = Rank::where('min_hours', '<=', $user->flying_hours/60)->orderBy('min_hours', 'desc')->first()->id;
            } else {
                $user->rank_id = null;
            }
            $user->save();
        });

        static::deleted(function ($pirep) {
            $user = User::find(Auth::user()->id);
            $user->flying_hours = Pirep::where('user_id', $user->id)->sum('computed_flight_time');
            $user->save();

            // Update rank if valid
            if ($user->rank_id && isset($user->rank_id)) {
                $user->rank_id = Rank::where('min_hours', '<=', $user->flying_hours/60)->orderBy('min_hours', 'desc')->first()->id;
            } else {
                $user->rank_id = null;
            }
            $user->save();
        });
    }
}
