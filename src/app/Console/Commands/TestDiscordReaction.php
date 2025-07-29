<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DiscordReactionService;
use App\Models\Event;
use App\Models\DiscordEventMessage;

class TestDiscordReaction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-discord-reaction {event_id} {discord_user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Discord reaction handling for event registration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $eventId = $this->argument('event_id');
        $discordUserId = $this->argument('discord_user_id');

        $this->info("Testing Discord reaction for event ID: {$eventId}");
        $this->info("Discord user ID: {$discordUserId}");

        // Find the event
        $event = Event::find($eventId);
        if (!$event) {
            $this->error("Event not found with ID: {$eventId}");
            return 1;
        }

        // Find the Discord message mapping
        $messageMapping = DiscordEventMessage::where('event_id', $eventId)->first();
        if (!$messageMapping) {
            $this->error("No Discord message mapping found for event ID: {$eventId}");
            return 1;
        }

        $this->info("Found Discord message ID: {$messageMapping->discord_message_id}");

        // Simulate reaction data
        $reactionData = [
            'channel_id' => $messageMapping->discord_channel_id,
            'message_id' => $messageMapping->discord_message_id,
            'user_id' => $discordUserId,
            'emoji' => [
                'name' => '✅'
            ]
        ];

        $this->info("Simulating reaction added...");
        
        $reactionService = new DiscordReactionService();
        $reactionService->handleReactionAdded($reactionData);

        $this->info("✅ Reaction test completed!");
        $this->info("Check the logs for reaction handling details.");

        return 0;
    }
} 