<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\FlightType;

class FlightTypesController extends Controller
{
    public function jxCreateEditFlightType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'flight_type' => 'required|string|max:255',
            'multiplier' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $validator->errors();
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        $flightType = FlightType::createFlightType($request->all(), $mode);
        if (!$flightType) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = 'Failed to create flight type';
            return response()->json($this->errorBag);
        }
        return response()->json([
            'message' => $mode == 'create' ? 'Flight type created successfully' : 'Flight type updated successfully',
            'flightType' => $flightType
        ]);
    }

    public function jxFetchFlightTypes(Request $request)
    {
        $flightTypes = FlightType::all();
        return response()->json([
            'message' => 'Flight types fetched successfully',
            'data' => $flightTypes
        ]);
    }

    public function jxDeleteFlightType(Request $request)
    {
        $flightType = FlightType::find($request->id);
        if (!$flightType) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = 'Flight type not found';
            return response()->json($this->errorBag);
        }
        if (!$flightType->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = 'Failed to delete flight type';
            return response()->json($this->errorBag);
        }
        $flightType->delete();
        return response()->json([
            'message' => 'Flight type deleted successfully',
        ]);
    }
}