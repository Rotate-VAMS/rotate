<?php

namespace App\Models;

use App\Models\Extended\_SystemSettings;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemSettings extends _SystemSettings
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'system_settings';

    protected $fillable = ['tenant_id', 'key', 'value'];
}
