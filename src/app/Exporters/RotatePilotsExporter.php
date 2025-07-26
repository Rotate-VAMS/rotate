<?php

namespace App\Exporters;

use App\Models\User;

use Illuminate\Support\Facades\Response;

class RotatePilotsExporter
{
    public function export()
    {
        $filename = 'pilots_export.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];
        
        $columns = ['name', 'email', 'rank', 'status', 'flying_hours', 'total_flights', 'member_since'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            $pilots = User::all();

            foreach ($pilots as $pilot) {
                fputcsv($file, [$pilot->name, $pilot->email, $pilot->rank->name, $pilot->status == User::PILOT_STATUS_ACTIVE ? 'Active' : 'Inactive', $this->getFlyingHours($pilot->flying_hours), $pilot->total_flights, $pilot->created_at->format('Y-m-d')]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    private function getFlyingHours($totalMinutes)
    {
        if ($totalMinutes === null || $totalMinutes === null) {
            return '-';
        }
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        return $hours . 'h ' . $minutes . 'm';
    }
}