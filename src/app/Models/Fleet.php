<?php

namespace App\Models;

use App\Models\Extended\_Fleet;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fleet extends _Fleet
{
    use SoftDeletes;

    protected $table = 'fleet';
    protected $fillable = ['aircraft', 'livery'];

    public function routes()
    {
        return $this->hasMany(Route::class, 'fleet_id');
    }
}
