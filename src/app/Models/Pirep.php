<?php

namespace App\Models;

use App\Models\Extended\_Pirep;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Pirep extends _Pirep
{
    use SoftDeletes;

    protected $table = 'pireps';
    protected $fillable = ['user_id', 'route_id', 'flight_time', 'flight_type_id', 'computed_flight_time'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected static function booted()
    {
        static::created(function ($pirep) {
            $user = User::find(Auth::user()->id);
            $user->flying_hours = Pirep::where('user_id', $user->id)->sum('computed_flight_time');
            $user->save();

            // Update rank if valid
            $user->rank_id = Rank::where('min_hours', '<=', $user->flying_hours/60)->orderBy('min_hours', 'desc')->first()->id;
            $user->save();
        });

        static::updated(function ($pirep) {
            $user = User::find(Auth::user()->id);
            $user->flying_hours = Pirep::where('user_id', $user->id)->sum('computed_flight_time');
            $user->save();

            // Update rank if valid
            $user->rank_id = Rank::where('min_hours', '<=', $user->flying_hours/60)->orderBy('min_hours', 'desc')->first()->id;
            $user->save();
        });

        static::deleted(function ($pirep) {
            $user = User::find(Auth::user()->id);
            $user->flying_hours = Pirep::where('user_id', $user->id)->sum('computed_flight_time');
            $user->save();

            // Update rank if valid
            $user->rank_id = Rank::where('min_hours', '<=', $user->flying_hours/60)->orderBy('min_hours', 'desc')->first()->id;
            $user->save();
        });
    }
}
