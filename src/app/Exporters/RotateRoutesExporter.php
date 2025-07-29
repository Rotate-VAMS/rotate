<?php

namespace App\Exporters;

use App\Models\Route;
use Illuminate\Support\Facades\Response;
use App\Models\CustomFieldValues;
use App\Models\CustomFieldConfiguration;
use App\Models\Rank;

class RotateRoutesExporter
{
    public function export()
    {
        $filename = 'routes_export.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $columns = ['flight_number', 'fleet_ids', 'origin_icao', 'destination_icao', 'min_rank', 'distance', 'flight_time', 'status'];

        $customFields = CustomFieldConfiguration::where('source_type', CustomFieldValues::SOURCE_TYPE_ROUTES)->get();
        if ($customFields->count() > 0) {
            $customFieldsColumns = $customFields->map(function ($field) {
                return $field->name;
            });
        } else {
            $customFieldsColumns = [];
        }

        $callback = function () use ($columns, $customFieldsColumns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, array_merge($columns, $customFieldsColumns ?? []));

            $routes = Route::where('tenant_id', app('currentTenant')->id)->get();

            foreach ($routes as $route) {
                // Decode fleet_ids from JSON
                $fleetIds = json_decode($route->fleet_ids, true);
                $fleetIdsString = implode(',', $fleetIds);
                                
                // Get custom fields
                $customFields = CustomFieldValues::getAllCustomFieldValues(CustomFieldValues::SOURCE_TYPE_ROUTES, $route->id);
                
                $row = [
                    $route->flight_number,
                    $fleetIdsString,
                    $route->origin,
                    $route->destination,
                    Rank::find($route->min_rank_id)->name . ' (' . $route->min_rank_id . ')',
                    $route->distance . ' NM',
                    $this->getFlightTime($route->flight_time),
                    $route->status,
                ];
                
                // Add custom fields if any
                if (!empty($customFieldsColumns)) {
                    foreach ($customFieldsColumns as $fieldName) {
                        $row[] = $customFields[$fieldName] ?? '';
                    }
                }
                
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function getFlightTime($distance)
    {
        if ($distance === null || $distance === null) {
            return '-';
        }
        $hours = floor($distance / 60);
        $minutes = $distance % 60;
        return $hours . 'h ' . $minutes . 'm';
    }
}