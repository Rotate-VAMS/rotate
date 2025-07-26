<?php

namespace App\Listeners;

use App\Events\EventCreated;
use App\Services\DiscordNotificationService;
use Illuminate\Support\Facades\Log;

class SendDiscordEventNotification
{

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(EventCreated $event): void
    {
        try {
            Log::info('=== EVENT LISTENER TRIGGERED ===');
            Log::info('EventCreated event triggered for event ID: ' . $event->event->id);
            Log::info('Event name: ' . $event->event->event_name);
            
            $discordService = new DiscordNotificationService();
            $success = $discordService->sendEventNotification($event->event);
            
            if ($success) {
                Log::info('Discord notification sent successfully for event ID: ' . $event->event->id);
            } else {
                Log::error('Failed to send Discord notification for event ID: ' . $event->event->id);
            }
            
            Log::info('=== EVENT LISTENER COMPLETED ===');
        } catch (\Exception $e) {
            Log::error('Failed to send Discord notification for event: ' . $e->getMessage());
            Log::error('Exception trace: ' . $e->getTraceAsString());
        }
    }
} 