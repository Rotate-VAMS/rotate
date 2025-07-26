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
use App\Importers\RotateRoutesImporter;
use App\Exporters\RotateRoutesExporter;
use Illuminate\Support\Facades\Cache;

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
        $scope = $request->scope ?? 'all';
        $cacheKey = 'routes:list:' . $scope;
        $routesData = Cache::store('redis')->remember($cacheKey, 1800, function () use ($scope) {
            $routes = Route::all();
            if ($scope === 'active') {
                $routes = $routes->where('status', Route::ROUTE_STATUS_ACTIVE);
            }
            if ($scope === 'inactive') {
                $routes = $routes->where('status', Route::ROUTE_STATUS_INACTIVE);
            }
            if ($scope === 'pireps') {
                // This is per-user, do not cache globally
                $userRank = Auth::user()->rank_id;
                $routes = $routes->where('min_rank_id', '<=', $userRank)->where('status', Route::ROUTE_STATUS_ACTIVE);
            }
            $routes = $routes->map(function ($route) {
                $route->origin_icao = $route->origin;
                $route->destination_icao = $route->destination;
                $route->route = $route->origin . '-' . $route->destination;
                $route->name_route = RotateAirportHelper::icaoToCity($route->origin) . ' - ' . RotateAirportHelper::icaoToCity($route->destination);
                $route->fleet_ids = json_decode($route->fleet_ids, true);
                $route->fleet_names = Fleet::whereIn('id', array_values($route->fleet_ids))->get()->toArray();
                $route->flight_time = $route->flight_time;
                $route->rank_id = $route->min_rank_id;
                $route->minimum_rank = Rank::find($route->min_rank_id)->name;
                $route->custom_fields = CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_ROUTES, $route->id);
                return $route;
            });
            $analyticsData = [
                'totalRoutes' => Route::count(),
                'activeRoutes' => Route::where('status', Route::ROUTE_STATUS_ACTIVE)->count(),
            ];
            return ['routes' => $routes, 'analytics' => $analyticsData];
        });
        return response()->json([
            'message' => 'Routes fetched successfully',
            'data' => $routesData['routes'],
            'analytics' => $routesData['analytics']
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
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        $route = Route::createEditRoute($request->all(), $mode);
        if (isset($route['error'])) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $route['error'];
            return response()->json($this->errorBag);
        }
        Cache::store('redis')->forget('routes:list:all');
        Cache::store('redis')->forget('routes:list:active');
        Cache::store('redis')->forget('routes:list:inactive');
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
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Route not found';
            return response()->json($this->errorBag);
        }
        if (!CustomFieldValues::deleteCustomFieldValues(CustomFieldValues::SOURCE_TYPE_ROUTES, $route->id)) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to delete custom field values';
            return response()->json($this->errorBag);
        }
        $route->delete();
        Cache::store('redis')->forget('routes:list:all');
        Cache::store('redis')->forget('routes:list:active');
        Cache::store('redis')->forget('routes:list:inactive');
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

    public function jxToggleRouteStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }
        $route = Route::find($request->id);
        if (!$route) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Route not found';
            return response()->json($this->errorBag);
        }
        $route->status = $route->status == Route::ROUTE_STATUS_ACTIVE ? Route::ROUTE_STATUS_INACTIVE : Route::ROUTE_STATUS_ACTIVE;
        if (!$route->save()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = 'Failed to update route status';
            return response()->json($this->errorBag);
        }
        return response()->json([
            'hasErrors' => false,
            'message' => 'Route status updated successfully'
        ]);
    }

    public function jxImportRoutes(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:txt,csv',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['message'] = $validator->errors()->first();
            return response()->json($this->errorBag);
        }
        $file = $request->file('file');
        $importer = new RotateRoutesImporter();
        $result = $importer->import($file);
        return response()->json([
            'hasErrors' => $result['hasErrors'],
            'message' => $result['message'],
        ]);
    }

    public function jxExportRoutes()
    {
        $exporter = new RotateRoutesExporter();
        return $exporter->export();
    }
}
