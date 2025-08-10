<?php

namespace App\Services;

use App\Models\DiscordSettings;
use App\Models\Event;
use App\Models\Pirep;
use App\Models\DiscordEventMessage;
use App\Models\DiscordPirepMessage;
use App\Helpers\RotateAirportHelper;
use Discord\Discord;
use Discord\Parts\Channel\Channel;
use Discord\WebSockets\Intents;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DiscordNotificationService
{
    protected $token;

    public function __construct()
    {
        $this->token = config('services.discord.token');
    }

    /**
     * Send event creation notification to Discord
     */
    public function sendEventNotification(Event $event): bool
    {
        try {
            Log::info('=== DISCORD NOTIFICATION SERVICE CALLED ===');
            Log::info('Event ID: ' . $event->id);
            Log::info('Event Name: ' . $event->event_name);
            Log::info('Timestamp: ' . now());
            
            // Check if notification has already been sent for this event using database
            $existingMessage = \App\Models\DiscordEventMessage::where('event_id', $event->id)
                ->where('tenant_id', app('currentTenant')->id)
                ->first();
            
            if ($existingMessage) {
                Log::warning("Discord notification already sent for event {$event->id}, skipping duplicate");
                return true; // Return true since notification was already sent
            }
            
            // Verify the event belongs to the current tenant
            if ($event->tenant_id !== app('currentTenant')->id) {
                Log::warning("Discord notification skipped: Event {$event->id} does not belong to current tenant");
                return false;
            }

            $channelId = DiscordSettings::getEventNotificationChannel();
            
            if (!$channelId) {
                Log::info('Discord event notification channel not configured for current tenant');
                return false;
            }

            $message = $this->formatEventMessage($event);
            
            // Use Discord REST API instead of WebSocket for more reliability
            $result = $this->sendMessageViaRestApi($channelId, $message);
            
            if ($result['success']) {
                // Store the message mapping for reaction handling
                $this->storeMessageMapping($event, $result['message_id'], $result['channel_id']);
                
                // Add initial reaction to the message
                $this->addInitialReaction($result['channel_id'], $result['message_id']);
            }
            
            Log::info('=== DISCORD NOTIFICATION SERVICE COMPLETED ===');
            return $result['success'];

        } catch (\Exception $e) {
            Log::error('Discord notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store message mapping for reaction handling
     */
    protected function storeMessageMapping(Event $event, ?string $messageId, string $channelId): void
    {
        try {
            if (!$messageId) {
                Log::warning("No message ID received for event {$event->id}");
                return;
            }
            
            DiscordEventMessage::storeMessageMapping($event->id, $messageId, $channelId);
            Log::info("Stored Discord message mapping for event {$event->id} with message ID {$messageId}");
        } catch (\Exception $e) {
            Log::error('Failed to store message mapping: ' . $e->getMessage());
        }
    }

    /**
     * Add initial reaction to the event message
     */
    protected function addInitialReaction(string $channelId, string $messageId): void
    {
        try {
            // Add the ✅ reaction to make it easy for users to click
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
                'Content-Type' => 'application/json',
            ])->put("https://discord.com/api/v10/channels/{$channelId}/messages/{$messageId}/reactions/✅/@me");

            if ($response->successful()) {
                Log::info("Added initial ✅ reaction to message {$messageId}");
            } else {
                Log::error("Failed to add initial reaction: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to add initial reaction: ' . $e->getMessage());
        }
    }

    /**
     * Send message using Discord REST API
     */
    protected function sendMessageViaRestApi(string $channelId, string $message): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post("https://discord.com/api/v10/channels/{$channelId}/messages", [
                'content' => $message
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info('Discord event notification sent successfully via REST API');
                return [
                    'success' => true,
                    'message_id' => $responseData['id'] ?? null,
                    'channel_id' => $channelId
                ];
            } else {
                Log::error('Discord REST API error: ' . $response->body());
                return ['success' => false];
            }
        } catch (\Exception $e) {
            Log::error('Discord REST API exception: ' . $e->getMessage());
            return ['success' => false];
        }
    }

    /**
     * Format event message for Discord
     */
    protected function formatEventMessage(Event $event): string
    {
        $eventDate = date('F j, Y \a\t g:i A', $event->event_date_time);
        $originCity = RotateAirportHelper::icaoToCity($event->origin);
        $destinationCity = RotateAirportHelper::icaoToCity($event->destination);
        
        $aircraft = json_decode($event->aircraft, true);
        $aircraftList = is_array($aircraft) ? implode(', ', $aircraft) : $event->aircraft;

        $message = "🎉 **New Event Created!** 🎉\n\n";
        $message .= "**{$event->event_name}**\n";
        $message .= "📝 {$event->event_description}\n\n";
        $message .= "📅 **Date & Time:** {$eventDate}\n";
        $message .= "🛫 **Origin:** {$event->origin} ({$originCity})\n";
        $message .= "🛬 **Destination:** {$event->destination} ({$destinationCity})\n";
        $message .= "✈️ **Aircraft:** {$aircraftList}\n\n";
        $message .= "**Click the ✅ reaction below to register for this event!**\n";
        $message .= "Join us for this exciting event! 🚀";

        return $message;
    }

    /**
     * Send PIREP creation notification to Discord
     */
    public function sendPirepNotification(Pirep $pirep): bool
    {
        try {
            Log::info('=== DISCORD PIREP NOTIFICATION SERVICE CALLED ===');
            Log::info('PIREP ID: ' . $pirep->id);
            Log::info('User ID: ' . $pirep->user_id);
            Log::info('Timestamp: ' . now());
            
            // Check if notification has already been sent for this PIREP using database
            $existingMessage = DiscordPirepMessage::where('pirep_id', $pirep->id)
                ->where('tenant_id', app('currentTenant')->id)
                ->first();
            
            if ($existingMessage) {
                Log::warning("Discord notification already sent for PIREP {$pirep->id}, skipping duplicate");
                return true; // Return true since notification was already sent
            }
            
            // Verify the PIREP belongs to the current tenant
            if ($pirep->tenant_id !== app('currentTenant')->id) {
                Log::warning("Discord notification skipped: PIREP {$pirep->id} does not belong to current tenant");
                return false;
            }

            $channelId = DiscordSettings::getPirepNotificationChannel();
            
            if (!$channelId) {
                Log::info('Discord PIREP notification channel not configured for current tenant');
                return false;
            }

            $message = $this->formatPirepMessage($pirep);
            
            // Use Discord REST API instead of WebSocket for more reliability
            $result = $this->sendMessageViaRestApi($channelId, $message);
            
            if ($result['success']) {
                // Store the message mapping for reaction handling
                $this->storePirepMessageMapping($pirep, $result['message_id'], $result['channel_id']);
                
                // Add initial reaction to the message
                $this->addInitialReaction($result['channel_id'], $result['message_id']);
            }
            
            Log::info('=== DISCORD PIREP NOTIFICATION SERVICE COMPLETED ===');
            return $result['success'];

        } catch (\Exception $e) {
            Log::error('Discord PIREP notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store PIREP message mapping for reaction handling
     */
    protected function storePirepMessageMapping(Pirep $pirep, ?string $messageId, string $channelId): void
    {
        try {
            if (!$messageId) {
                Log::warning("No message ID received for PIREP {$pirep->id}");
                return;
            }
            
            DiscordPirepMessage::storeMessageMapping($pirep->id, $messageId, $channelId);
            Log::info("Stored Discord PIREP message mapping for PIREP {$pirep->id} with message ID {$messageId}");
        } catch (\Exception $e) {
            Log::error('Failed to store PIREP message mapping: ' . $e->getMessage());
        }
    }

    /**
     * Format PIREP message for Discord
     */
    protected function formatPirepMessage(Pirep $pirep): string
    {
        $user = $pirep->user;
        $hours = floor($pirep->flight_time / 60);
        $minutes = $pirep->flight_time % 60;
        $computedHours = floor($pirep->computed_flight_time / 60);
        $computedMinutes = $pirep->computed_flight_time % 60;
        
        $message = "✈️ **New PIREP Filed!** ✈️\n\n";
        $message .= "👨‍✈️ **Pilot:** {$user->name} | ({$user->callsign})\n";
        $message .= "🕐 **Flight Time:** {$hours}h {$minutes}m\n";
        $message .= "📊 **Computed Time:** {$computedHours}h {$computedMinutes}m\n";
        
        // Add route information if available
        if ($pirep->route_id) {
            $route = $pirep->route;
            $originCity = RotateAirportHelper::icaoToCity($route->origin);
            $destinationCity = RotateAirportHelper::icaoToCity($route->destination);
            
            $message .= "🛫 **Origin:** {$route->origin} ({$originCity})\n";
            $message .= "🛬 **Destination:** {$route->destination} ({$destinationCity})\n";
        } elseif ($pirep->event_id) {
            $event = $pirep->event;
            $originCity = RotateAirportHelper::icaoToCity($event->origin);
            $destinationCity = RotateAirportHelper::icaoToCity($event->destination);
            
            $message .= "🎯 **Event:** {$event->event_name}\n";
            $message .= "🛫 **Origin:** {$event->origin} ({$originCity})\n";
            $message .= "🛬 **Destination:** {$event->destination} ({$destinationCity})\n";
        }
        
        $message .= "\n🎉 **Great job, pilot!** 🎉";

        return $message;
    }

    /**
     * Test Discord connection and channel
     */
    public function testConnection(string $channelId): bool
    {
        try {
            $testMessage = "✅ Discord integration test successful! This channel is now configured for notifications.";
            $result = $this->sendMessageViaRestApi($channelId, $testMessage);
            
            if ($result['success']) {
                Log::info('Discord test message sent successfully via REST API');
            } else {
                Log::error('Discord test message failed via REST API');
            }
            
            return $result['success'];
        } catch (\Exception $e) {
            Log::error('Discord test connection error: ' . $e->getMessage());
            return false;
        }
    }
} 