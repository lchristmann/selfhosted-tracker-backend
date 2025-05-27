<?php

namespace App\Filters\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LocationFilter {
    public function transform(Request $request): array
    {
        $eloQuery = [];

        $timezone = $request->query('tz', 'UTC');
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            $timezone = 'UTC'; // fallback
        }

        // Check if 'date' is specified (single day filter)
        $date = $request->query('date');
        if ($date) {
            // Parse date in timezone and get day start/end timestamps
            $dayStart = Carbon::parse($date, $timezone)->startOfDay()->getTimestampMs();
            $dayEnd = Carbon::parse($date, $timezone)->endOfDay()->getTimestampMs();

            $eloQuery[] = ['timestamp', '>=', $dayStart];
            $eloQuery[] = ['timestamp', '<=', $dayEnd];

            // If 'date' is specified, ignore from & to filters
            return $eloQuery;
        }

        // Check if 'from' is specified (start date filter)
        $from = $request->query('from');
        if ($from) {
            $fromTimestamp = Carbon::parse($from, $timezone)->startOfDay()->getTimestampMs();
            $eloQuery[] = ['timestamp', '>=', $fromTimestamp];
        }

        // Check if 'to' is specified (end date filter)
        $to = $request->query('to');
        if ($to) {
            $toTimestamp = Carbon::parse($to, $timezone)->endOfDay()->getTimestampMs();
            $eloQuery[] = ['timestamp', '<=', $toTimestamp];
        }

        return $eloQuery;
    }
}
