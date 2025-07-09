<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fleet extends Model
{
    use SoftDeletes;

    protected $table = 'fleet';
    protected $fillable = ['aircraft', 'livery'];

    public function routes()
    {
        return $this->hasMany(Route::class, 'fleet_id');
    }
}
