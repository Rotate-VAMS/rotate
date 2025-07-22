<?php

namespace Modules\Events\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Documents;
use Illuminate\Http\Request;
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
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        $events = Event::all();
        foreach ($events as $event) {
            $cover_image = Documents::fetchDocument(Documents::ENTITY_TYPE_EVENT, $event->id);
            if (isset($cover_image['error'])) {
                $cover_image = null;
            }
            $event->cover_image = $cover_image;
        }
        return response()->json([
            'hasErrors' => false,
            'data' => $events
        ]);
    }
}
