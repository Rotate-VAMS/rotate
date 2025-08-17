<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Extended\_Notifications;

class Notifications extends _Notifications
{
    use SoftDeletes;

    protected $table = 'notifications';

    protected $primaryKey = 'id';

    protected $fillable = [
        'tenant_id',
        'notification_type',
        'sent_status',
    ];
}
