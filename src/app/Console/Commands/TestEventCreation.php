<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use App\Events\EventCreated;

class TestEventCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-event-creation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test event creation and Discord notification';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing event creation and Discord notification...');

        // Create a test event
        $event = new Event();
        $event->event_name = 'Test Event - Discord Notification ' . time();
        $event->event_description = 'This is a test event to verify Discord notifications are working.';
        $event->event_date_time = time() + 86400; // Tomorrow
        $event->origin = 'VABB';
        $event->destination = 'VOBL';
        $event->aircraft = json_encode(['A320', 'B737']);
        $event->save();

        $this->info("Created test event with ID: {$event->id}");

        // Manually dispatch the event multiple times to test duplicate prevention
        $this->info('Dispatching EventCreated event multiple times to test duplicate prevention...');
        
        for ($i = 1; $i <= 3; $i++) {
            $this->info("Dispatch attempt {$i}...");
            EventCreated::dispatch($event);
            sleep(1); // Small delay between dispatches
        }

        $this->info('✅ Event creation test completed!');
        $this->info('Check your Discord channel for the notification.');
        $this->info('Check the logs for any errors or duplicate prevention messages.');

        return 0;
    }
} 