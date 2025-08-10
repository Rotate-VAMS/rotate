<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DiscordNotificationService;
use App\Models\DiscordSettings;
use App\Models\Pirep;
use App\Models\User;
use App\Models\FlightType;
use App\Models\Route;

class TestPirepDiscordNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-pirep-discord-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PIREP Discord notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing PIREP Discord notification system...');

        // Check if PIREP notifications are enabled
        $pirepActivityEnabled = DiscordSettings::getSetting(DiscordSettings::DISCORD_BOT_PIREP_ACTIVITY, DiscordSettings::DISCORD_PIREP_ACTIVITY_DISABLED);
        if ($pirepActivityEnabled != DiscordSettings::DISCORD_PIREP_ACTIVITY_ENABLED) {
            $this->error('PIREP Discord notifications are disabled. Please enable them in the admin panel.');
            return 1;
        }

        // Check if channel is configured
        $channelId = DiscordSettings::getPirepNotificationChannel();
        
        if (!$channelId) {
            $this->error('Discord PIREP notification channel not configured. Please set it in the admin panel.');
            return 1;
        }

        $this->info("Channel ID configured: {$channelId}");

        // Test the connection
        $discordService = new DiscordNotificationService();
        $success = $discordService->testConnection($channelId);

        if ($success) {
            $this->info('✅ Discord PIREP notification test successful!');
            $this->info('Check your Discord channel for the test message.');
            return 0;
        } else {
            $this->error('❌ Discord PIREP notification test failed!');
            $this->error('Please check:');
            $this->error('1. Bot token is correct');
            $this->error('2. Channel ID is correct');
            $this->error('3. Bot has permission to send messages in the channel');
            return 1;
        }
    }
}
