<?php

namespace App\Models\Extended;

use Illuminate\Database\Eloquent\Model;

class _SystemSettings extends Model
{
    const LEADERBOARD_POINTS_CONFIGURATION = 'leaderboard_points_configuration';

    const SETTING_ENABLED = 1;

    const SETTING_DISABLED = 0;

    public static function getSystemSetting($key)
    {
        return self::where('key', $key)->first();
    }

    public static function toggleSystemSetting($key)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) {
            return ['error' => 'System setting not found'];
        }
        $setting->value = $setting->value == self::SETTING_ENABLED ? self::SETTING_DISABLED : self::SETTING_ENABLED;
        if (! $setting->save()) {
            return ['error' => 'Failed to update system setting'];
        }
        return $setting;
    }
}