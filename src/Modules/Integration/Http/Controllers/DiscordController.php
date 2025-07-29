<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DiscordSettings;
use App\Services\DiscordNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiscordController extends Controller
{
    /**
     * Get Discord settings
     */
    public function jxGetDiscordSettings()
    {
        $channelId = DiscordSettings::getEventNotificationChannel();
        $discordBotEventActivity = DiscordSettings::getSetting(DiscordSettings::DISCORD_BOT_EVENT_ACTIVITY);
        return response()->json([
            'hasErrors' => false,
            'data' => [
                'event_notification_channel_id' => $channelId,
                'discord_bot_event_activity' => $discordBotEventActivity
            ]
        ]);
    }

    /**
     * Update Discord settings
     */
    public function jxUpdateDiscordSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_notification_channel_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'hasErrors' => true,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            if ($request->has('event_notification_channel_id')) {
                DiscordSettings::setEventNotificationChannel($request->event_notification_channel_id);
            }

            return response()->json([
                'hasErrors' => false,
                'message' => 'Discord settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'hasErrors' => true,
                'message' => 'Failed to update Discord settings: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Test Discord connection
     */
    public function jxTestDiscordConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_notification_channel_id' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'hasErrors' => true,
                'message' => $validator->errors()->first()
            ]);
        }

        try {
            $discordService = new DiscordNotificationService();
            $success = $discordService->testConnection($request->event_notification_channel_id);

            if ($success) {
                return response()->json([
                    'hasErrors' => false,
                    'message' => 'Discord connection test successful!'
                ]);
            } else {
                return response()->json([
                    'hasErrors' => true,
                    'message' => 'Discord connection test failed. Please check your bot token and channel ID.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'hasErrors' => true,
                'message' => 'Discord connection test failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Toggle Discord bot event activity
     */
    public function jxToggleDiscordBotEventActivity()
    {
        $currentValue = DiscordSettings::getSetting(DiscordSettings::DISCORD_BOT_EVENT_ACTIVITY, DiscordSettings::DISCORD_EVENT_ACTIVITY_DISABLED);
        $newValue = $currentValue == DiscordSettings::DISCORD_EVENT_ACTIVITY_DISABLED ? DiscordSettings::DISCORD_EVENT_ACTIVITY_ENABLED : DiscordSettings::DISCORD_EVENT_ACTIVITY_DISABLED;
        
        DiscordSettings::setSetting(DiscordSettings::DISCORD_BOT_EVENT_ACTIVITY, $newValue);

        return response()->json([
            'hasErrors' => false,
            'message' => 'Discord bot event activity ' . ($newValue == DiscordSettings::DISCORD_EVENT_ACTIVITY_DISABLED ? 'disabled' : 'enabled') . ' successfully'
        ]);
    }
} 