<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\User;
use App\Models\DiscordSettings;
use App\Models\DiscordEventMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class DiscordReactionService
{
    protected $token;

    public function __construct()
    {
        $this->token = config('services.discord.token');
    }

    /**
     * Handle reaction added event
     */
    public function handleReactionAdded($data): void
    {
        try {
            $channelId = $data['channel_id'] ?? null;
            $messageId = $data['message_id'] ?? null;
            $userId = $data['user_id'] ?? null;
            $emoji = $data['emoji']['name'] ?? null;

            Log::info("Discord reaction received: {$emoji} from user {$userId} on message {$messageId}");

            // Only handle reactions in the event notification channel
            $notificationChannelId = DiscordSettings::getEventNotificationChannel();
            if ($channelId !== $notificationChannelId) {
                return;
            }

            // Only handle specific emoji reactions (e.g., ✅ for registration)
            if ($emoji !== '✅' && $emoji !== '🎉') {
                return;
            }

            Log::info("Discord reaction received: {$emoji} from user {$userId} on message {$messageId}");

            // Find the event by message ID (we'll need to store this mapping)
            $event = $this->findEventByMessageId($messageId);
            if (!$event) {
                Log::warning("No event found for message ID: {$messageId}");
                return;
            }

            // Verify the event belongs to the current tenant
            if ($event->tenant_id !== app('currentTenant')->id) {
                Log::warning("Discord reaction skipped: Event {$event->id} does not belong to current tenant");
                return;
            }

            // Find user by Discord ID for current tenant
            $user = User::where('discord_id', $userId)
                ->where('tenant_id', app('currentTenant')->id)
                ->first();
            if (!$user) {
                Log::warning("No user found for Discord ID: {$userId} in current tenant");
                $this->sendPrivateMessage($userId, "❌ Your Discord account is not linked to a Rotate account. Please link your account first.");
                return;
            }

            // Check if user is already registered
            $existingAttendance = EventAttendance::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->where('tenant_id', app('currentTenant')->id)
                ->first();

            if ($existingAttendance) {
                Log::info("User {$user->id} is already registered for event {$event->id}");
                $this->sendPrivateMessage($userId, "ℹ️ You are already registered for this event!");
                return;
            }

            // Register user for event
            $attendance = new EventAttendance();
            $attendance->event_id = $event->id;
            $attendance->user_id = $user->id;
            $attendance->tenant_id = app('currentTenant')->id;
            $attendance->created_at = now();
            $attendance->updated_at = now();

            if ($attendance->save()) {
                Log::info("User {$user->id} successfully registered for event {$event->id}");
                $this->sendPrivateMessage($userId, "✅ You have been successfully registered for the event: **{$event->event_name}**");
                
                // Update the original message to show registration
                $this->updateEventMessage($channelId, $messageId, $event);
            } else {
                Log::error("Failed to register user {$user->id} for event {$event->id}");
                $this->sendPrivateMessage($userId, "❌ Failed to register for the event. Please try again.");
            }

        } catch (\Exception $e) {
            Log::error('Discord reaction handling error: ' . $e->getMessage());
        }
    }

    /**
     * Handle reaction removed event (unregister)
     */
    public function handleReactionRemoved($data): void
    {
        try {
            $channelId = $data['channel_id'] ?? null;
            $messageId = $data['message_id'] ?? null;
            $userId = $data['user_id'] ?? null;
            $emoji = $data['emoji']['name'] ?? null;

            // Only handle reactions in the event notification channel
            $notificationChannelId = DiscordSettings::getEventNotificationChannel();
            if ($channelId !== $notificationChannelId) {
                return;
            }

            // Only handle specific emoji reactions
            if ($emoji !== '✅' && $emoji !== '🎉') {
                return;
            }

            Log::info("Discord reaction removed: {$emoji} from user {$userId} on message {$messageId}");

            // Find the event by message ID
            $event = $this->findEventByMessageId($messageId);
            if (!$event) {
                return;
            }

            // Verify the event belongs to the current tenant
            if ($event->tenant_id !== app('currentTenant')->id) {
                Log::warning("Discord reaction removal skipped: Event {$event->id} does not belong to current tenant");
                return;
            }

            // Find user by Discord ID for current tenant
            $user = User::where('discord_id', $userId)
                ->where('tenant_id', app('currentTenant')->id)
                ->first();
            if (!$user) {
                return;
            }

            // Remove user registration
            $attendance = EventAttendance::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->where('tenant_id', app('currentTenant')->id)
                ->first();

            if ($attendance) {
                $attendance->delete();
                Log::info("User {$user->id} unregistered from event {$event->id}");
                $this->sendPrivateMessage($userId, "✅ You have been unregistered from the event: **{$event->event_name}**");
                
                // Update the original message
                $this->updateEventMessage($channelId, $messageId, $event);
            }

        } catch (\Exception $e) {
            Log::error('Discord reaction removal error: ' . $e->getMessage());
        }
    }

    /**
     * Find event by Discord message ID
     */
    protected function findEventByMessageId(string $messageId): ?Event
    {
        return DiscordEventMessage::findEventByMessageId($messageId);
    }

    /**
     * Send private message to user
     */
    protected function sendPrivateMessage(string $userId, string $message): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
                'Content-Type' => 'application/json',
            ])->post("https://discord.com/api/v10/users/@me/channels", [
                'recipient_id' => $userId
            ]);

            if ($response->successful()) {
                $channelData = $response->json();
                $dmChannelId = $channelData['id'];

                $response = Http::withHeaders([
                    'Authorization' => 'Bot ' . $this->token,
                    'Content-Type' => 'application/json',
                ])->post("https://discord.com/api/v10/channels/{$dmChannelId}/messages", [
                    'content' => $message
                ]);

                return $response->successful();
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Failed to send private message: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Update event message with current attendance count
     */
    protected function updateEventMessage(string $channelId, string $messageId, Event $event): void
    {
        try {
            // Verify the event belongs to the current tenant
            if ($event->tenant_id !== app('currentTenant')->id) {
                Log::warning("Event message update skipped: Event {$event->id} does not belong to current tenant");
                return;
            }

            $attendees = EventAttendance::where('event_id', $event->id)
                ->where('tenant_id', app('currentTenant')->id)
                ->get();
            $attendeeCount = $attendees->count();

            $message = $this->formatEventMessageWithAttendance($event, $attendeeCount);

            $response = Http::withHeaders([
                'Authorization' => 'Bot ' . $this->token,
                'Content-Type' => 'application/json',
            ])->patch("https://discord.com/api/v10/channels/{$channelId}/messages/{$messageId}", [
                'content' => $message
            ]);

            if ($response->successful()) {
                Log::info("Updated event message for event {$event->id}");
            } else {
                Log::error("Failed to update event message: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Failed to update event message: ' . $e->getMessage());
        }
    }

    /**
     * Format event message with attendance count
     */
    protected function formatEventMessageWithAttendance(Event $event, int $attendeeCount): string
    {
        $eventDate = date('F j, Y \a\t g:i A', $event->event_date_time);
        $originCity = \App\Helpers\RotateAirportHelper::icaoToCity($event->origin);
        $destinationCity = \App\Helpers\RotateAirportHelper::icaoToCity($event->destination);
        
        $aircraft = json_decode($event->aircraft, true);
        $aircraftList = is_array($aircraft) ? implode(', ', $aircraft) : $event->aircraft;

        $message = "🎉 **New Event Created!** 🎉\n\n";
        $message .= "**{$event->event_name}**\n";
        $message .= "📝 {$event->event_description}\n\n";
        $message .= "📅 **Date & Time:** {$eventDate}\n";
        $message .= "🛫 **Origin:** {$event->origin} ({$originCity})\n";
        $message .= "🛬 **Destination:** {$event->destination} ({$destinationCity})\n";
        $message .= "✈️ **Aircraft:** {$aircraftList}\n\n";
        $message .= "👥 **Attendees:** {$attendeeCount}\n\n";
        $message .= "**Click the ✅ reaction below to register for this event!**\n";
        $message .= "Join us for this exciting event! 🚀";

        return $message;
    }
} 