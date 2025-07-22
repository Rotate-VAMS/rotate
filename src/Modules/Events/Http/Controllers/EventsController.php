<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Documents;
use App\Models\EventAttendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class EventsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Breadcrumbs
        $breadcrumbs = [
            [
                'title' => 'Events'
            ]
        ];
        return Inertia::render('Events/Pages/Events', ['breadcrumbs' => $breadcrumbs]);
    }

    public function jxCreateEditEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_name' => 'required|string|max:255',
            'event_description' => 'required|string|max:255',
            'event_date_time' => 'required|date',
            'origin' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'aircraft' => 'array',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::createEditEvent($request->all(), $request->id ? 'edit' : 'create');
        if (isset($event['error'])) {
            return response()->json(['error' => $event['error']], 422);
        }
        return response()->json([
            'hasErrors' => false,
            'message' => $request->id ? 'Event updated successfully' : 'Event created successfully'
        ]);
    }

    public function jxDeleteEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::deleteEvent($request->id);
        if (isset($event['error'])) {
            return response()->json(['error' => $event['error']], 422);
        }
        return response()->json([
            'hasErrors' => false,
            'message' => $event['success']
        ]);
    }

    public function jxFetchEvents(Request $request)
    {
        $events = Event::where('event_date_time', '>=', time())->orderBy('event_date_time', 'asc')->get();
        foreach ($events as $event) {
            $event->attendees = EventAttendance::where('event_id', $event->id)->get()->pluck('user_id')->toArray();
        }
        foreach ($events as $event) {
            $cover_image = Documents::fetchDocument(Documents::ENTITY_TYPE_EVENT, $event->id);
            if (isset($cover_image['error'])) {
                $cover_image = Documents::DEFAULT_IMAGE;
            }
            $event->cover_image = $cover_image;
        }
        return response()->json([
            'hasErrors' => false,
            'data' => $events
        ]);
    }

    public function jxRegisterForEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::find($request->id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $eventAttendance = new EventAttendance();
        $eventAttendance->event_id = $event->id;
        $eventAttendance->user_id = Auth::user()->id;
        $eventAttendance->created_at = time();
        $eventAttendance->updated_at = time();

        if (!$eventAttendance->save()) {
            return response()->json(['error' => 'Failed to register for event'], 422);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Event registered successfully'
        ]);
    }

    public function jxDeRegisterForEvent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $event = Event::find($request->id);

        if (!$event) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        $eventAttendance = EventAttendance::where('event_id', $event->id)->where('user_id', Auth::user()->id)->first();
        if (!$eventAttendance) {
            return response()->json(['error' => 'Event not found'], 404);
        }

        if (!$eventAttendance->delete()) {
            return response()->json(['error' => 'Failed to deregister for event'], 422);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Event deregistered successfully'
        ]);
    }
}
