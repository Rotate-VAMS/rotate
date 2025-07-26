<?php

namespace App\Models;

use App\Models\Extended\_Rank;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Rank extends _Rank
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'ranks';
    protected $fillable = ['name', 'min_hours'];

    public function users()
    {
        return $this->hasMany(User::class, 'rank_id');
    }
}
