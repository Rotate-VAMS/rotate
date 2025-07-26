<?php

namespace App\Importers;

use App\Models\Route;
use App\Models\Rank;
use App\Models\Fleet;

class RotateRoutesImporter
{
    public function import($file)
    {
        $file = fopen($file, 'r');
        $headers = fgetcsv($file); // Skip header
        $availableRanks = Rank::all()->pluck('id')->toArray();
        $availableFleets = Fleet::all()->pluck('id')->toArray();

        $imported = 0;
        while (($row = fgetcsv($file)) !== false) {
            // Handle the case where JSON array gets split by CSV parser
            $flightNumber = $row[0];
            
            // Check if the fleet_ids JSON array is complete in one column or split
            $fleetIdsJson = '';
            $currentIndex = 1;
            
            // Check if the first column after flight number contains a complete JSON array
            if (isset($row[$currentIndex]) && strpos($row[$currentIndex], '[') !== false && strpos($row[$currentIndex], ']') !== false) {
                // Complete JSON array in one column (e.g., [1] or [1,2,3])
                $fleetIdsJson = $row[$currentIndex];
                $currentIndex = 2;
            } else {
                // JSON array is split across multiple columns (e.g., [1, 2, 3] becomes [1, 2, 3])
                $fleetIdsJson = $row[$currentIndex];
                $currentIndex++;
                
                // Continue adding parts until we find the closing bracket
                while ($currentIndex < count($row) && strpos($row[$currentIndex], ']') === false) {
                    $fleetIdsJson .= ',' . $row[$currentIndex];
                    $currentIndex++;
                }
                
                // Add the closing part
                if ($currentIndex < count($row)) {
                    $fleetIdsJson .= ',' . $row[$currentIndex];
                    $currentIndex++;
                }
            }
            
            // Extract remaining fields
            $origin_icao = $row[$currentIndex] ?? null;
            $destination_icao = $row[$currentIndex + 1] ?? null;
            $min_rank = $row[$currentIndex + 2] ?? null;
            $status = $row[$currentIndex + 3] ?? null;
            
            // Add validation for required fields
            if (!$flightNumber || !$fleetIdsJson || !$origin_icao || !$destination_icao || !$min_rank || !$status) {
                return ['hasErrors' => true, 'message' => 'Invalid data in row ' . ($imported + 1)];
            }

            // Add validation for status
            if ($status != 1 && $status != 0) {
                return ['hasErrors' => true, 'message' => 'Invalid status in row ' . ($imported + 1)];
            }

            // Add validation for min_rank
            if (!in_array($min_rank, $availableRanks)) {
                return ['hasErrors' => true, 'message' => 'Invalid min_rank in row ' . ($imported + 1)];
            }

            // Add validation for fleet_ids
            $fleedIds = json_decode($fleetIdsJson, true);
            foreach ($fleedIds as $fleedId) {
                if (!in_array($fleedId, $availableFleets)) {
                    return ['hasErrors' => true, 'message' => 'Invalid fleet_ids in row ' . ($imported + 1)];
                }
            }

            $data = [
                'flight_number' => $flightNumber,
                'fleet_ids' => $fleedIds,
                'origin_icao' => $origin_icao,
                'destination_icao' => $destination_icao,
                'use_aircraft_rank' => false,
                'rank_id' => $min_rank,
                'status' => $status,
            ];

            $importResult = Route::createEditRoute($data, 'create');
            if (isset($importResult['error'])) {
                return ['hasErrors' => true, 'message' => 'Failed to import route in row ' . ($imported + 1) . ': ' . $importResult['error']];
            }

            $imported++;
        }

        fclose($file);

        return ['hasErrors' => false, 'message' => $imported . ' routes imported successfully'];
    }
}