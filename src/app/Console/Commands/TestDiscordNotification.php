<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DiscordNotificationService;
use App\Models\DiscordSettings;

class TestDiscordNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-discord-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Discord notification system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Discord notification system...');

        // Check if channel is configured
        $channelId = DiscordSettings::getEventNotificationChannel();
        
        if (!$channelId) {
            $this->error('Discord notification channel not configured. Please set it in the admin panel.');
            return 1;
        }

        $this->info("Channel ID configured: {$channelId}");

        // Test the connection
        $discordService = new DiscordNotificationService();
        $success = $discordService->testConnection($channelId);

        if ($success) {
            $this->info('✅ Discord notification test successful!');
            $this->info('Check your Discord channel for the test message.');
            return 0;
        } else {
            $this->error('❌ Discord notification test failed!');
            $this->error('Please check:');
            $this->error('1. Bot token is correct');
            $this->error('2. Channel ID is correct');
            $this->error('3. Bot has permission to send messages in the channel');
            return 1;
        }
    }
} 