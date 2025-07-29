<?php

namespace App\Models;

use App\Models\Extended\_Leaderboard;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class Leaderboard extends _Leaderboard
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'leaderboard_points_configuration';

    protected $fillable = ['tenant_id', 'leaderboard_event_name', 'points'];
}
