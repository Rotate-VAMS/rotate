<?php

namespace Database\Seeders;

use App\Models\DiscordSettings;
use Illuminate\Database\Seeder;

class DiscordSettingsSeeder extends Seeder
{
    public function run()
    {
        $discordSettings = new DiscordSettings();
        $discordSettings->setting_key = 'discord_bot_event_activity';
        $discordSettings->setting_value = DiscordSettings::DISCORD_EVENT_ACTIVITY_DISABLED;
        $discordSettings->tenant_id = 1;
        $discordSettings->created_at = now();
        $discordSettings->updated_at = now();
        $discordSettings->save();
    }
}