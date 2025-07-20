<?php

namespace Modules\Pirep\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\CustomFieldConfiguration;
use App\Models\Pirep;
use App\Models\CustomFieldValues;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

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

    public function jxFetchPireps()
    {
        $pireps = DB::table('pireps')
            ->leftJoin('routes', 'pireps.route_id', '=', 'routes.id')
            ->leftJoin('flight_types', 'pireps.flight_type_id', '=', 'flight_types.id')
            ->leftJoin('users', 'pireps.user_id', '=', 'users.id')
            ->select('pireps.*', 'routes.flight_number', 'routes.origin', 'routes.destination', 'routes.distance', 'flight_types.flight_type as flight_type_name', 'users.name as pilot_name')
            ->get();

        foreach ($pireps as $pirep) {
            $pirep->route = $pirep->origin . ' - ' . $pirep->destination;
            $pirep->custom_fields = CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_PIREPS, $pirep->id);
            $pirep->flight_time = $pirep->flight_time;
            $pirep->computed_flight_time = $pirep->computed_flight_time;
        }

        return response()->json(['message' => 'Pireps fetched successfully', 'data' => $pireps]);
    }

    public function jxCreateEditPirep(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route_id' => 'required|exists:routes,id',
            'flight_time_hours' => 'required|integer|min:0',
            'flight_time_minutes' => 'required|integer|min:0',
            'flight_type_id' => 'required|exists:flight_types,id',
        ]);
        if ($validator->fails()) {
            return response()->json(['hasErrors' => true, 'errors' => $validator->errors()]);
        }

        // Validate if user has achieved the minimum rank of the route
        $route = Route::find($request->route_id);
        $user = Auth::user();
        if ($user->rank_id < $route->min_rank_id && $route->min_rank_id !== null) {
            return response()->json(['hasErrors' => true, 'errors' => 'You have not achieved the minimum rank of the route']);
        }

        $mode = $request->id ? 'edit' : 'create';
        $response = Pirep::createEditPirep($request->all(), $mode);
        if (isset($response['error'])) {
            return response()->json(['hasErrors' => true, 'errors' => $response['error']]);
        }
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
}
