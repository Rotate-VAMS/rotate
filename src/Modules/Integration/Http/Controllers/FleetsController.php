<?php

namespace Modules\Integration\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Fleet;
use App\Models\Rank;

class FleetsController extends Controller
{
    public function jxFetchAllFleets(Request $request)
    {
        $file = storage_path('app/helpers/'.Fleet::FLEET_DATA_FILE);
    
        if (!file_exists($file)) {
            return response()->json([
                'hasErrors' => true,
                'message' => 'Fleet data file not found.'
            ], 404);
        }
    
        $handle = fopen($file, 'r');
    
        // Read first line (headers) and trim BOM if needed
        $firstLine = fgets($handle);
        $firstLine = preg_replace('/^\xEF\xBB\xBF/', '', $firstLine); // remove UTF-8 BOM
        $headers = str_getcsv($firstLine); // safer than fgetcsv if BOM present
    
        $airlineIndex = null;
        $aircraftIndex = null;
    
        foreach ($headers as $index => $header) {
            if (stripos($header, 'Airline') !== false && stripos($header, 'Parent') === false && $airlineIndex === null) {
                $airlineIndex = $index;
            }
            if (stripos($header, 'Aircraft Type') !== false && $aircraftIndex === null) {
                $aircraftIndex = $index;
            }
        }
    
        if ($airlineIndex === null || $aircraftIndex === null) {
            return response()->json([
                'hasErrors' => true,
                'message' => 'Required columns not found in CSV.'
            ], 400);
        }
    
        $fleetPairs = [];
    
        // Process the remaining lines as CSV
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($headers)) {
                continue;
            }
    
            $airline = trim($row[$airlineIndex]);
            $aircraftType = trim($row[$aircraftIndex]);
    
            if ($airline && $aircraftType) {
                $fleetPairs[] = "{$airline} - {$aircraftType}";
            }
        }
    
        fclose($handle);
    
        // Deduplicate
        $fleetPairs = array_values(array_unique($fleetPairs));
    
        return response()->json([
            'hasErrors' => false,
            'data' => $fleetPairs
        ]);
    }

    public function jxCreateEditFleet(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'selected_fleet' => 'array',
            'selected_fleet.*' => 'string|max:255',
            'minimum_rank' => 'required_if:id,null|integer',
        ]);

        if ($validator->fails()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $validator->errors();
            return response()->json($this->errorBag);
        }

        $mode = $request->id ? 'edit' : 'create';
        $result = Fleet::createEditFleet($request->all(), $mode);
        if ($result['hasErrors']) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = $result['errors'];
            return response()->json($this->errorBag);
        }

        return response()->json([
            'hasErrors' => false,
            'message' => $result['message']
        ]);
    }

    public function jxFetchFleets(Request $request)
    {
        $fleets = Fleet::all();
        foreach ($fleets as $fleet) {
            if ($fleet->minimum_rank) {
                $fleet->minimum_rank = Rank::find($fleet->minimum_rank)->name;
            } else {
                $fleet->minimum_rank = "None";
            }
        }
        return response()->json([
            'hasErrors' => false,
            'data' => $fleets
        ]);
    }

    public function jxDeleteFleet(Request $request)
    {
        $fleet = Fleet::find($request->id);
        if (!$fleet) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Fleet not found'];
            return response()->json($this->errorBag);
        }
    
        if (!$fleet->delete()) {
            $this->errorBag['hasErrors'] = true;
            $this->errorBag['errors'] = ['Failed to delete fleet'];
            return response()->json($this->errorBag);
        }
    
        return response()->json([
            'hasErrors' => false,
            'message' => 'Fleet deleted successfully'
        ]);
    }
}
