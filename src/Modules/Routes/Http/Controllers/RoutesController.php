<?php

namespace Modules\Routes\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Fleet;
use App\Models\Route;
use App\Models\Rank;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Helpers\RotateAirportHelper;
use App\Models\CustomFieldConfiguration;
use App\Models\CustomFieldValues;
use Illuminate\Support\Facades\Auth;

class RoutesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Breadcrumbs
        $breadcrumbs = [
            [
                'title' => 'Routes'
            ]
        ];
        $analyticsData = [
            'total_routes' => Route::count(),
            'total_active_routes' => Route::where('status', Route::ROUTE_STATUS_ACTIVE)->count(),
        ];
        return Inertia::render('Routes/Pages/Routes', [
            'breadcrumbs' => $breadcrumbs,
            'analyticsData' => $analyticsData
        ]);
    }

    public function jxFetchRoutes(Request $request)
    {
        $scope = $request->scope;
        $routes = Route::all();
        if ($scope === 'active') {
            $routes = $routes->where('status', Route::ROUTE_STATUS_ACTIVE);
        }
        if ($scope === 'inactive') {
            $routes = $routes->where('status', Route::ROUTE_STATUS_INACTIVE);
        }
        if ($scope === 'pireps') {
            $userRank = Auth::user()->rank_id;
            $routes = $routes->where('min_rank_id', '<=', $userRank)->where('status', Route::ROUTE_STATUS_ACTIVE);
        }

        $routes = $routes->map(function ($route) {
            $route->origin_icao = $route->origin;
            $route->destination_icao = $route->destination;
            $route->route = $route->origin . '-' . $route->destination;
            $route->name_route = RotateAirportHelper::icaoToCity($route->origin) . ' - ' . RotateAirportHelper::icaoToCity($route->destination);
            $route->fleet_ids = json_decode($route->fleet_ids);
            $route->fleet_names = Fleet::whereIn('id', $route->fleet_ids)->pluck('livery', 'aircraft')->toArray();
            $route->flight_time = $route->flight_time . ' hours';
            $route->rank_id = $route->min_rank_id;
            $route->minimum_rank = Rank::find($route->min_rank_id)->name;
            $route->custom_fields = CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_ROUTES, $route->id);
            return $route;
        });

        return response()->json([
            'message' => 'Routes fetched successfully',
            'data' => $routes
        ]);
    }

    public function jxCreateEditRoutes(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'flight_number' => 'required|string|max:255',
            'origin_icao' => 'required|string|max:4',
            'destination_icao' => 'required|string|max:4',
            'fleet_ids' => 'required|array',
            'fleet_ids.*' => 'required|integer',
            'use_aircraft_rank' => 'required|boolean',
            'rank_id' => 'required_if:use_aircraft_rank,false|exists:ranks,id',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $validator->errors();
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        $route = Route::createEditRoute($request->all(), $mode);
        if (isset($route['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $route['error'];
            return response()->json($this->errorBag);
        }
        return response()->json([
            'message' => 'Route saved successfully',
            'route' => $route
        ]);
    }

    public function jxDeleteRoutes(Request $request)
    {
        // Validate the request
        $request->validate([
            'id' => 'required|integer',
        ]);
        $route = Route::find($request->id);
        if (!$route) {
            return response()->json(['error' => 'Route not found']);
        }
        if (!CustomFieldValues::deleteCustomFieldValues(CustomFieldValues::SOURCE_TYPE_ROUTES, $route->id)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Failed to delete custom field values'];
            return response()->json($this->errorBag);
        }
        $route->delete();
        return response()->json([
            'hasErrors' => false,
            'message' => 'Route deleted successfully'
        ]);
    }

    public function jxGetRouteCustomFields()
    {
        $customFields = CustomFieldConfiguration::getCustomFields(CustomFieldConfiguration::SOURCE_TYPE_ROUTES);
        return response()->json([
            'hasErrors' => false,
            'data' => $customFields
        ]);
    }
}
