<?php

namespace App\Models\Extended;

use App\Models\Documents;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\CustomFieldValues;
use Illuminate\Database\Eloquent\Model;

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
