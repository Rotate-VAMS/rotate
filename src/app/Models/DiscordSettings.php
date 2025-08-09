<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\BelongsToTenant;

class DiscordSettings extends Model
{
    use BelongsToTenant;

    protected $table = 'discord_settings';
    
    protected $fillable = [
        'setting_key',
        'setting_value'
    ];

    const DISCORD_BOT_EVENT_ACTIVITY = 'discord_bot_event_activity';

    const DISCORD_BOT_PIREP_ACTIVITY = 'discord_bot_pirep_activity';

    const DISCORD_EVENT_NOTIFICATION_CHANNEL_ID = 'event_notification_channel_id';

    const DISCORD_PIREP_NOTIFICATION_CHANNEL_ID = 'pirep_notification_channel_id';

    const DISCORD_EVENT_ACTIVITY_ENABLED = 1;

    const DISCORD_EVENT_ACTIVITY_DISABLED = 0;

    const DISCORD_PIREP_ACTIVITY_ENABLED = 1;

    const DISCORD_PIREP_ACTIVITY_DISABLED = 0;

    /**
     * Get a setting value by key for current tenant
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = self::where('setting_key', $key)
            ->where('tenant_id', app('currentTenant')->id)
            ->first();
        return $setting ? $setting->setting_value : $default;
    }

    /**
     * Set a setting value for current tenant
     */
    public static function setSetting(string $key, $value): bool
    {
        $setting = self::where('setting_key', $key)
            ->where('tenant_id', app('currentTenant')->id)
            ->first();
        
        if ($setting) {
            $setting->setting_value = $value;
            return $setting->save();
        } else {
            return self::create([
                'setting_key' => $key,
                'setting_value' => $value,
                'tenant_id' => app('currentTenant')->id
            ])->save();
        }
    }

    /**
     * Get event notification channel ID for current tenant
     */
    public static function getEventNotificationChannel(): ?string
    {
        return self::getSetting(self::DISCORD_EVENT_NOTIFICATION_CHANNEL_ID);
    }

    /**
     * Set event notification channel ID for current tenant
     */
    public static function setEventNotificationChannel(string $channelId): bool
    {
        return self::setSetting(self::DISCORD_EVENT_NOTIFICATION_CHANNEL_ID, $channelId);
    }

    /**
     * Get pirep notification channel ID for current tenant
     */
    public static function getPirepNotificationChannel(): ?string
    {
        return self::getSetting(self::DISCORD_PIREP_NOTIFICATION_CHANNEL_ID);
    }

    /**
     * Set pirep notification channel ID for current tenant
     */
    public static function setPirepNotificationChannel(string $channelId): bool
    {
        return self::setSetting(self::DISCORD_PIREP_NOTIFICATION_CHANNEL_ID, $channelId);
    }
} 