<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscordSettings extends Model
{
    protected $table = 'discord_settings';
    
    protected $fillable = [
        'setting_key',
        'setting_value'
    ];

    const DISCORD_BOT_EVENT_ACTIVITY = 'discord_bot_event_activity';

    const DISCORD_EVENT_NOTIFICATION_CHANNEL_ID = 'event_notification_channel_id';

    const DISCORD_EVENT_ACTIVITY_ENABLED = 1;

    const DISCORD_EVENT_ACTIVITY_DISABLED = 0;

    /**
     * Get a setting value by key
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value
     */
    public static function setSetting(string $key, $value): bool
    {
        $setting = self::where('setting_key', $key)->first();
        
        if ($setting) {
            $setting->setting_value = $value;
            return $setting->save();
        } else {
            return self::create([
                'setting_key' => $key,
                'setting_value' => $value
            ])->save();
        }
    }

    /**
     * Get event notification channel ID
     */
    public static function getEventNotificationChannel(): ?string
    {
        return self::getSetting(self::DISCORD_EVENT_NOTIFICATION_CHANNEL_ID);
    }

    /**
     * Set event notification channel ID
     */
    public static function setEventNotificationChannel(string $channelId): bool
    {
        return self::setSetting(self::DISCORD_EVENT_NOTIFICATION_CHANNEL_ID, $channelId);
    }
} 