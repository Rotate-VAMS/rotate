<?php

namespace App\Models;

use App\Models\Extended\_Rank;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rank extends _Rank
{
    use SoftDeletes;

    protected $table = 'ranks';
    protected $fillable = ['name', 'min_hours'];

    public function users()
    {
        return $this->hasMany(User::class, 'rank_id');
    }
}
