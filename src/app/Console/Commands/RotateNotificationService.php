<?php

namespace App\Console\Commands;

use App\Helpers\RotateConstants;
use Illuminate\Console\Command;
use App\Models\Notifications;
use Illuminate\Support\Facades\Log;
use App\Models\Tenant;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantRegistrationMail;
use App\Mail\TenantPlanActivationMail;
use App\Mail\TenantPlanExpireMail;

class RotateNotificationService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rotate-notification-service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command used to send the notification emails for different events to all the tenants';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $notifications = Notifications::getPendingNotifications();
        Log::info('Found ' . $notifications->count() . ' pending notifications at ' . now());

        if ($notifications->count() > RotateConstants::CONSTANT_FOR_ZERO) {
            foreach ($notifications as $notification) {
                $tenant = Tenant::find($notification->tenant_id);
                $emailSentStatus = self::sendNotification($tenant, $notification->notification_type);
                if ($emailSentStatus == Notifications::NOTIFICATION_SENT_STATUS_SENT) {
                    Notifications::updateNotificationSentStatus($notification->id, Notifications::NOTIFICATION_SENT_STATUS_SENT);
                } else {
                    Notifications::updateNotificationSentStatus($notification->id, Notifications::NOTIFICATION_SENT_STATUS_FAILED);
                    Log::info('Notification failed to send for tenant ' . $tenant->name . ' at ' . now());
                }
            }
        }
    }

    public function sendNotification(Tenant $tenant, $notificationType)
    {
        switch ($notificationType) {
            case Notifications::NOTIFICATION_TYPE_NEW_REGISTRATION:
                return self::sendNewRegistrationNotification($tenant);
            case Notifications::NOTIFICATION_TYPE_PLAN_ACTIVATION:
                return self::sendPlanActivationNotification($tenant);
            case Notifications::NOTIFICATION_TYPE_PLAN_EXPIRATION:
                return self::sendPlanExpirationNotification($tenant);
        }
    }

    private function sendNewRegistrationNotification(Tenant $tenant)
    {
        $adminEmail = $tenant->admin_email;
        $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_PENDING;

        try {
            Mail::to($adminEmail)->send(new TenantRegistrationMail($tenant));
            $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_SENT;
            Log::info('Welcome email sent successfully for tenant ' . $tenant->name . ' at ' . now());
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_FAILED;
        }
        return $emailSentStatus;
    }

    private function sendPlanActivationNotification(Tenant $tenant)
    {
        $adminEmail = $tenant->admin_email;
        $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_PENDING;

        try {
            Mail::to($adminEmail)->send(new TenantPlanActivationMail($tenant));
            $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_SENT;
            Log::info('Plan activation email sent successfully for tenant ' . $tenant->name . ' at ' . now());
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_FAILED;
        }
        return $emailSentStatus;
    }

    private function sendPlanExpirationNotification(Tenant $tenant)
    {
        $adminEmail = $tenant->admin_email;
        $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_PENDING;

        try {
            Mail::to($adminEmail)->send(new TenantPlanExpireMail($tenant));
            $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_SENT;
            Log::info('Plan expiration email sent successfully for tenant ' . $tenant->name . ' at ' . now());
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
            $emailSentStatus = Notifications::NOTIFICATION_SENT_STATUS_FAILED;
        }
        return $emailSentStatus;
    }
}
