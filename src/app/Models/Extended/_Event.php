<?php

namespace App\Models\Extended;

use App\Models\Documents;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\CustomFieldValues;
use App\Models\DiscordSettings;
use App\Events\EventCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class _Event extends Model
{
    public static function createEditEvent($data, $mode)
    {
        if ($mode === 'edit') {
            $event = Event::find($data['id']);
            if (!$event) {
                return ['error' => 'Event not found'];
            }
        } else {
            $event = new Event();
        }
        $event->event_name = $data['event_name'];
        $event->event_description = $data['event_description'];
        $event->event_date_time = strtotime($data['event_date_time']);
        $event->origin = $data['origin'];
        $event->destination = $data['destination'];
        $event->aircraft = json_encode($data['aircraft']);
        $event->created_at = now();
        $event->updated_at = now();
        
        if (!$event->save()) {
            return ['error' => 'Failed to create event'];
        }

        // Handle cover image
        if (isset($data['cover_image'])) {
            $document = Documents::createEditDocument(Documents::ENTITY_TYPE_EVENT, $event->id, $data['cover_image']);
            if (isset($document['error'])) {
                return ['error' => $document['error']];
            }
        }

        // Handle custom fields
        if (isset($data['customData'])) {
            foreach ($data['customData'] as $field_key => $value) {
                CustomFieldValues::createCustomFieldValue(CustomFieldValues::SOURCE_TYPE_EVENTS, $event->id, $field_key, $value);
            }
        }

        // Dispatch event for Discord notification (only for new events)
        if ($mode === 'create' && DiscordSettings::getSetting(DiscordSettings::DISCORD_BOT_EVENT_ACTIVITY) == DiscordSettings::DISCORD_EVENT_ACTIVITY_ENABLED) {
            Log::info('=== DISPATCHING EVENT CREATED ===');
            Log::info('Event ID: ' . $event->id);
            Log::info('Event Name: ' . $event->event_name);
            Log::info('Dispatch timestamp: ' . now());
            Log::info('Process ID: ' . getmypid());
            EventCreated::dispatch($event);
            Log::info('=== EVENT DISPATCHED ===');
        }

        return $event;
    }

    public static function deleteEvent($id)
    {
        // Check if event is having any document attached
        $document = Documents::fetchDocument(Documents::ENTITY_TYPE_EVENT, $id);
        if ($document) {
            Documents::deleteDocument(Documents::ENTITY_TYPE_EVENT, $id);
        }

        // Cleanup any event attendance records
        EventAttendance::deleteEventAttendance($id);

        $event = Event::find($id);
        if (!$event) {
            return ['error' => 'Event not found'];
        }
        $event->delete();

        return ['success' => 'Event deleted successfully'];
    }
}
