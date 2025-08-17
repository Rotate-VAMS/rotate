<?php

namespace App\Models\Extended;

use Illuminate\Database\Eloquent\Model;

class _Notifications extends Model
{
    const NOTIFICATION_SENT_STATUS_PENDING = 0;

    const NOTIFICATION_SENT_STATUS_SENT = 1;
    
    const NOTIFICATION_SENT_STATUS_FAILED = 2;

    const NOTIFICATION_TYPE_NEW_REGISTRATION = 1;

    const NOTIFICATION_TYPE_PLAN_ACTIVATION = 2;

    const NOTIFICATION_TYPE_PLAN_EXPIRATION = 3;
    
    public static function getPendingNotifications()
    {
        return self::whereIn('sent_status', [self::NOTIFICATION_SENT_STATUS_PENDING, self::NOTIFICATION_SENT_STATUS_FAILED])->get();
    }

    public static function updateNotificationSentStatus($notificationId, $sentStatus)
    {
        return self::where('id', $notificationId)->update(['sent_status' => $sentStatus]);
    }

    public static function getNotificationById($notificationId)
    {
        return self::where('id', $notificationId)->first();
    }

    public static function getNotificationByType($notificationType)
    {
        return self::where('notification_type', $notificationType)->get();
    }

    public static function getNotificationByTenantId($tenantId)
    {
        return self::where('tenant_id', $tenantId)->get();
    }

    public static function getNotificationByTenantIdAndType($tenantId, $notificationType)
    {
        return self::where('tenant_id', $tenantId)->where('notification_type', $notificationType)->get();
    }

    public static function getNotificationByTenantIdAndSentStatus($tenantId, $sentStatus)
    {
        return self::where('tenant_id', $tenantId)->where('sent_status', $sentStatus)->get();
    }

    public static function createNotification($tenantId, $notificationType, $sentStatus = self::NOTIFICATION_SENT_STATUS_PENDING)
    {
        $notification = new self();
        $notification->tenant_id = $tenantId;
        $notification->notification_type = $notificationType;
        $notification->sent_status = $sentStatus;
        
        if (! $notification->save()) {
            return null;
        }
        return $notification;
    }

    public static function deleteNotification($notificationId)
    {
        return self::where('id', $notificationId)->delete();
    }
}