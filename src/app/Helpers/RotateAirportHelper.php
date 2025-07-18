<?php

namespace App\Helpers;

class RotateAirportHelper
{
    protected static ?array $airportData = null;

    // Load CSV into memory (once)
    protected static function loadCsv(): void
    {
        if (self::$airportData !== null) return;

        $file = storage_path('app/helpers/airports.csv');

        if (!file_exists($file)) {
            self::$airportData = [];
            return;
        }

        $handle = fopen($file, 'r');
        $headers = fgetcsv($handle); // First row

        $data = [];
        while (($row = fgetcsv($handle)) !== false) {
            $entry = array_combine($headers, $row);
            if (!empty($entry['icao_code'])) {
                $data[strtoupper($entry['icao_code'])] = $entry;
            }
        }

        fclose($handle);
        self::$airportData = $data;
    }

    // Get city name from ICAO
    public static function icaoToCity(string $icao): ?string
    {
        self::loadCsv();
        $icao = strtoupper($icao);

        return self::$airportData[$icao]['municipality'] ?? null;
    }

    // Get distance in NM between two ICAO codes
    public static function distanceBetweenICAOs(string $icao1, string $icao2): ?float
    {
        self::loadCsv();
        $icao1 = strtoupper($icao1);
        $icao2 = strtoupper($icao2);

        if (!isset(self::$airportData[$icao1]) || !isset(self::$airportData[$icao2])) {
            return null;
        }

        $lat1 = (float) self::$airportData[$icao1]['latitude_deg'];
        $lon1 = (float) self::$airportData[$icao1]['longitude_deg'];
        $lat2 = (float) self::$airportData[$icao2]['latitude_deg'];
        $lon2 = (float) self::$airportData[$icao2]['longitude_deg'];

        return self::haversineDistance($lat1, $lon1, $lat2, $lon2);
    }

    // Haversine formula in Nautical Miles
    protected static function haversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 3440.065; // Nautical miles

        $lat1 = deg2rad($lat1);
        $lon1 = deg2rad($lon1);
        $lat2 = deg2rad($lat2);
        $lon2 = deg2rad($lon2);

        $latDelta = $lat2 - $lat1;
        $lonDelta = $lon2 - $lon1;

        $a = sin($latDelta / 2) ** 2 + cos($lat1) * cos($lat2) * sin($lonDelta / 2) ** 2;
        $c = 2 * asin(sqrt($a));

        return round($earthRadius * $c, 2);
    }
}