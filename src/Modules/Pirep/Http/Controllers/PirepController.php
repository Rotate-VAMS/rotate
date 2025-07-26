<?php

namespace Modules\Pirep\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CustomFieldConfiguration;
use App\Models\Pirep;
use App\Models\CustomFieldValues;
use App\Models\FlightType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Helpers\RotateAirportHelper;
use Illuminate\Support\Facades\Cache;

class PirepController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $breadcrumbs = [
            [
                'title' => 'Pireps'
            ]
        ];
        return Inertia::render('Pireps/Pages/Pireps', ['breadcrumbs' => $breadcrumbs]);
    }

    public function jxFetchPireps(Request $request)
    {
        $filter = $request->filter;
        if ($filter === 'my') {
            // Do not cache per-user data
            $pireps = DB::table('pireps')
                ->leftJoin('routes', 'pireps.route_id', '=', 'routes.id')
                ->leftJoin('flight_types', 'pireps.flight_type_id', '=', 'flight_types.id')
                ->leftJoin('users', 'pireps.user_id', '=', 'users.id')
                ->select('pireps.*', 'routes.flight_number', 'routes.origin', 'routes.destination', 'routes.distance', 'flight_types.flight_type as flight_type_name', 'users.name as pilot_name', 'users.callsign')
                ->where('pireps.deleted_at', null)
                ->where('pireps.user_id', Auth::user()->id)
                ->orderBy('pireps.created_at', 'desc')
                ->get();
        } else {
            $pireps = Cache::store('redis')->remember('pireps:list:all', 1800, function () {
                $pireps = DB::table('pireps')
                    ->leftJoin('routes', 'pireps.route_id', '=', 'routes.id')
                    ->leftJoin('flight_types', 'pireps.flight_type_id', '=', 'flight_types.id')
                    ->leftJoin('users', 'pireps.user_id', '=', 'users.id')
                    ->select('pireps.*', 'routes.flight_number', 'routes.origin', 'routes.destination', 'routes.distance', 'flight_types.flight_type as flight_type_name', 'users.name as pilot_name', 'users.callsign')
                    ->where('pireps.deleted_at', null)
                    ->orderBy('pireps.created_at', 'desc')
                    ->get();
                foreach ($pireps as $pirep) {
                    $pirep->custom_fields = CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_PIREPS, $pirep->id);
                    $pirep->origin_city = RotateAirportHelper::icaoToCity($pirep->origin);
                    $pirep->destination_city = RotateAirportHelper::icaoToCity($pirep->destination);
                    $pirep->flight_time_hours = $pirep->flight_time;
                    $pirep->computed_flight_time = $pirep->computed_flight_time;
                    $pirep->multiplier = FlightType::find($pirep->flight_type_id)->multiplier ?? 1;
                    $pirep->airline = RotateAirportHelper::airlineToICAO(substr($pirep->flight_number, 0, 2)) ?? '-';
                }
                $analyticsData = [
                    'myPireps' => null, // Not cached globally
                    'totalPireps' => Pirep::where('deleted_at', null)->count()
                ];
                return ['pireps' => $pireps, 'analytics' => $analyticsData];
            });
        }
        if ($filter === 'my') {
            foreach ($pireps as $pirep) {
                $pirep->custom_fields = CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_PIREPS, $pirep->id);
                $pirep->origin_city = RotateAirportHelper::icaoToCity($pirep->origin);
                $pirep->destination_city = RotateAirportHelper::icaoToCity($pirep->destination);
                $pirep->flight_time_hours = $pirep->flight_time;
                $pirep->computed_flight_time = $pirep->computed_flight_time;
                $pirep->multiplier = FlightType::find($pirep->flight_type_id)->multiplier ?? 1;
                $pirep->airline = RotateAirportHelper::airlineToICAO(substr($pirep->flight_number, 0, 2)) ?? '-';
            }
            $analyticsData = [
                'myPireps' => Pirep::where('user_id', Auth::user()->id)->where('deleted_at', null)->count(),
                'totalPireps' => Pirep::where('deleted_at', null)->count()
            ];
            return response()->json(['message' => 'Pireps fetched successfully', 'data' => $pireps, 'analytics' => $analyticsData]);
        } else {
            return response()->json(['message' => 'Pireps fetched successfully', 'data' => $pireps['pireps'], 'analytics' => $pireps['analytics']]);
        }
    }

    public function jxCreateEditPirep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'flight_time_hours' => 'required|min:0',
            'flight_time_minutes' => 'required|min:0',
            'flight_type_id' => 'required|exists:flight_types,id',
        ]);
        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        // Validate if user has achieved the minimum rank of the route
        $route = Route::find($request->route_id);
        $user = Auth::user();
        if ($user->rank_id < $route->min_rank_id && $route->min_rank_id !== null) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'You have not achieved the minimum rank of the route';
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        $response = Pirep::createEditPirep($request->all(), $mode);
        if (isset($response['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $response['error'];
            return response()->json($this->errorBag);
        }
        Cache::store('redis')->forget('pireps:list:all');
        return response()->json(['hasErrors' => false, 'message' => $response['success']]);
    }

    public function jxGetPirepCustomFields()
    {
        $customFields = CustomFieldConfiguration::getCustomFields(CustomFieldConfiguration::SOURCE_TYPE_PIREPS);
        return response()->json([
            'hasErrors' => false,
            'data' => $customFields
        ]);
    }

    public function jxDeletePireps(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:pireps,id',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $pirep = Pirep::find($request->id);

        if (!CustomFieldValues::deleteCustomFieldValues(CustomFieldValues::SOURCE_TYPE_PIREPS, $pirep->id)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete custom field values';
            return response()->json($this->errorBag);
        }
        if (!$pirep->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete pirep';
            return response()->json($this->errorBag);
        }
        Cache::store('redis')->forget('pireps:list:all');
        return response()->json(['hasErrors' => false, 'message' => 'Pirep deleted successfully']);
    }
}
