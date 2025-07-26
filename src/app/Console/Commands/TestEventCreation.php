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
        $event->event_name = 'Test Event - Discord Notification';
        $event->event_description = 'This is a test event to verify Discord notifications are working.';
        $event->event_date_time = time() + 86400; // Tomorrow
        $event->origin = 'VABB';
        $event->destination = 'VOBL';
        $event->aircraft = json_encode(['A320', 'B737']);
        $event->save();

        $this->info("Created test event with ID: {$event->id}");

        // Manually dispatch the event
        $this->info('Dispatching EventCreated event...');
        EventCreated::dispatch($event);

        $this->info('✅ Event creation test completed!');
        $this->info('Check your Discord channel for the notification.');
        $this->info('Check the logs for any errors.');

        return 0;
    }
} 