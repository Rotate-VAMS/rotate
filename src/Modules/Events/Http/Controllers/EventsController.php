<?php

namespace Modules\Events\Http\Controllers;

use App\Helpers\RotateAirportHelper;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Documents;
use App\Models\EventAttendance;
use App\Models\CustomFieldConfiguration;
use App\Models\CustomFieldValues;
use App\Models\Pirep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use function App\Helpers\tenant_cache_remember;
use function App\Helpers\tenant_cache_forget;

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
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $event = Event::createEditEvent($request->all(), $request->id ? 'edit' : 'create');
        if (isset($event['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $event['error'];
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('events:list:upcoming');
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
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $event = Event::deleteEvent($request->id);
        if (isset($event['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $event['error'];
            return response()->json($this->errorBag);
        }
        tenant_cache_forget('events:list:upcoming');
        return response()->json([
            'hasErrors' => false,
            'message' => $event['success']
        ]);
    }

    public function jxFetchEvents(Request $request)
    {
        $cacheKey = 'events:list:upcoming';
        $events = tenant_cache_remember($cacheKey, 1800, function () {
            $events = Event::orderBy('event_date_time', 'asc')->get();
            foreach ($events as $event) {
                $event->attendees = EventAttendance::where('event_id', $event->id)->get()->pluck('user_id')->toArray();
                $event->custom_fields = CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_EVENTS, $event->id);
                $event->origin_city = RotateAirportHelper::icaoToCity($event->origin);
                $event->destination_city = RotateAirportHelper::icaoToCity($event->destination);
                $event->completed = $event->event_date_time < time();
                $cover_image = Documents::fetchDocument(Documents::ENTITY_TYPE_EVENT, $event->id);
                if (isset($cover_image['error'])) {
                    $cover_image = Documents::DEFAULT_IMAGE;
                }
                $event->cover_image = $cover_image;
            }
            $analyticsData = [
                'totalEvents' => Event::count(),
                'activeEvents' => $events->where('event_date_time', '>=', time())->count(),
            ];
            return ['events' => $events, 'analytics' => $analyticsData];
        });
        return response()->json([
            'hasErrors' => false,
            'data' => $events['events'],
            'analytics' => $events['analytics']
        ]);
    }

    public function jxRegisterForEvent(Request $request)
    {
        tenant_cache_forget('events:list:upcoming');
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $event = Event::find($request->id);

        if (!$event) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Event not found';
            return response()->json($this->errorBag);
        }

        $eventAttendance = new EventAttendance();
        $eventAttendance->event_id = $event->id;
        $eventAttendance->user_id = Auth::user()->id;
        $eventAttendance->created_at = time();
        $eventAttendance->updated_at = time();

        if (!$eventAttendance->save()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to register for event';
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Event registered successfully'
        ]);
    }

    public function jxDeRegisterForEvent(Request $request)
    {
        tenant_cache_forget('events:list:upcoming');
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $event = Event::find($request->id);

        if (!$event) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Event not found';
            return response()->json($this->errorBag);
        }

        $eventAttendance = EventAttendance::where('event_id', $event->id)->where('user_id', Auth::user()->id)->first();
        if (!$eventAttendance) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Event not found';
            return response()->json($this->errorBag);
        }

        if (!$eventAttendance->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to deregister for event';
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => 'Event deregistered successfully'
        ]);
    }

    public function jxFetchCustomFields(Request $request)
    {
        $customFields = CustomFieldConfiguration::getCustomFields(CustomFieldConfiguration::SOURCE_TYPE_EVENTS);
        return response()->json([
            'hasErrors' => false,
            'data' => $customFields
        ]);
    }

    public function jxFileEventPirep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required|integer',
            'flight_time_hours' => 'required|min:0',
            'flight_time_minutes' => 'required|min:0',
            'flight_type_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $event = Event::find($request->event_id);
        if (!$event) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Event not found';
            return response()->json($this->errorBag);
        }

        if ($event->event_date_time > time()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Event has not started yet';
            return response()->json($this->errorBag);
        }

        $pirep = Pirep::createEditPirep($request->all(), 'create', true);
        
        if (isset($pirep['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $pirep['error'];
            return response()->json($this->errorBag);
        }
        
        tenant_cache_forget('pireps:list:all');
        return response()->json([
            'hasErrors' => false,
            'message' => $pirep['success']
        ]);
    }

    public function jxEditEventPirep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
            'event_id' => 'required|integer',
            'flight_time_hours' => 'required|min:0',
            'flight_time_minutes' => 'required|min:0',
            'flight_type_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $event = Event::find($request->event_id);
        if (!$event) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Event not found';
            return response()->json($this->errorBag);
        }

        if (!Event::checkIsUserWasParticipant($event->id, Auth::user()->id)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'You are not a participant of this event';
            return response()->json($this->errorBag);
        }

        $pirep = Pirep::createEditPirep($request->all(), 'edit', true);
        
        if (isset($pirep['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $pirep['error'];
            return response()->json($this->errorBag);
        }
        
        tenant_cache_forget('pireps:list:all');
        return response()->json([
            'hasErrors' => false,
            'message' => $pirep['success']
        ]);
    }
}
