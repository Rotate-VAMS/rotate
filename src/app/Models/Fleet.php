<?php

namespace App\Models;

use App\Models\Extended\_Fleet;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\BelongsToTenant;

class Fleet extends _Fleet
{
    use SoftDeletes, BelongsToTenant;

    protected $table = 'fleet';
    protected $fillable = ['aircraft', 'livery'];

    public function routes()
    {
        return $this->hasMany(Route::class, 'fleet_id');
    }
}
