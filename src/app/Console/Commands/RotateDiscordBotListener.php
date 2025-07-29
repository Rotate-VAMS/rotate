<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Discord\Discord;
use Discord\WebSockets\Event;
use Discord\Parts\Channel\Message;
use Discord\WebSockets\Intents;
use App\Services\RotateDiscordBotService;
use App\Services\DiscordNotificationService;
use App\Services\DiscordReactionService;

class RotateDiscordBotListener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:rotate-discord-bot-listener';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rotate Discord Bot listener';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $this->info('Starting Discord bot...');
    
        $discord = new Discord([
            'token' => config('services.discord.token'),
            'intents' => Intents::getDefaultIntents() | Intents::MESSAGE_CONTENT,
        ]);
    
        $discord->on('ready', function (Discord $discord) {
            $this->info("Bot is ready!");

            $handler = app(RotateDiscordBotService::class); // Laravel's DI
            $notificationService = app(DiscordNotificationService::class); // Discord notification service
            $reactionService = app(DiscordReactionService::class); // Discord reaction service

            $discord->on(Event::MESSAGE_CREATE, function (Message $message) use ($handler) {
                if ($message->author->bot) return;

                $handler->handle($message);
            });

            // Handle reaction added events
            $discord->on(Event::MESSAGE_REACTION_ADD, function ($reaction) use ($reactionService) {
                $reactionService->handleReactionAdded($reaction);
            });

            // Handle reaction removed events
            $discord->on(Event::MESSAGE_REACTION_REMOVE, function ($reaction) use ($reactionService) {
                $reactionService->handleReactionRemoved($reaction);
            });
        });
    
        $discord->run();
    }
}
