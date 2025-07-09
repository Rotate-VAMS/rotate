<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rank extends Model
{
    use SoftDeletes;

    protected $table = 'ranks';
    protected $fillable = ['name', 'min_hours'];

    public function users()
    {
        return $this->hasMany(User::class, 'rank_id');
    }
}
