<?php

namespace App\Http\Controllers\Api\V1;

use App\Filters\V1\LocationFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreLocationsRequest;
use App\Http\Resources\V1\AggregatedLocationResource;
use App\Http\Resources\V1\LocationResource;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    /**
     * Display
     * - a listing of the resource for the current day, yesterday and the day before
     * - a listing of aggregated resources (the day's average) for the 30 days before that
     * or
     * - a listing of the resource for a given day (if the query parameter "date" is passed)
     * - a listing of the resource for the given interval (if the query parameter "from", "to", or both are passed)
     */
    public function indexByUser(User $user, Request $request): AnonymousResourceCollection|JsonResponse
    {
        $filter = new LocationFilter();
        $filterItems = $filter->transform($request);

        if ($filterItems) {
            $locations = $user->locations()
                ->where($filterItems)
                ->orderBy('timestamp', 'desc')
                ->get();

            return LocationResource::collection($locations);
        }

        return $this->getDefaultLocationListing($user, $request);
    }

    /**
     * Display a listing of the resource.
     */
    public function indexMy(Request $request): AnonymousResourceCollection|JsonResponse
    {
        return $this->indexByUser($request->user(), $request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function storeMy(StoreLocationsRequest $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $data = $request->validated();

        $locations = collect($data)->map(function ($locationData) use ($user) {
            return Location::create([
                'user_id' => $user->id,
                'latitude' => $locationData['latitude'],
                'longitude' => $locationData['longitude'],
                'timestamp' => $locationData['timestamp'],
            ]);
        });

        return LocationResource::collection($locations);
    }


    protected function getDefaultLocationListing(User $user, Request $request): JsonResponse
    {
        // Get timezone from query, default to UTC
        $timezone = $request->query('tz', 'UTC');
        if (!in_array($timezone, \DateTimeZone::listIdentifiers())) {
            $timezone = 'UTC';
        }

        // ---------- put the locations of the last 3 days together (today, yesterday and the day before) ----------
        $now = Carbon::now($timezone);
        $twoDaysAgo = $now->copy()->subDays(2)->startOfDay()->getTimestampMs();

        $locationsFromLast3Days = $user->locations()
            ->where('timestamp', '>=', $twoDaysAgo)
            ->orderBy('timestamp', 'desc')
            ->get();

        // ---------------- assemble the average locations of the 30 days before those three days ------------------
        $thirtyDaysStart = Carbon::createFromTimestampMs($twoDaysAgo, $timezone)
            ->copy()
            ->subDays(30)
            ->startOfDay()
            ->getTimestampMs();

        $thirtyDaysEnd = $twoDaysAgo;

        $averagedLocations = DB::table('locations')
            ->selectRaw("
                to_char(
                    timezone(?, to_timestamp(timestamp / 1000)),
                    'YYYY-MM-DD'
                ) as day,
                AVG(latitude) as latitude,
                AVG(longitude) as longitude,
                (
                    extract(epoch from (
                        date_trunc('day', to_timestamp(AVG(timestamp) / 1000) AT TIME ZONE ?)
                        + interval '12 hours'
                    ) AT TIME ZONE ?
                    ) * 1000
                ) as timestamp_noon_ms
            ", [$timezone, $timezone, $timezone])
            ->where('user_id', $user->id)
            ->whereBetween('timestamp', [$thirtyDaysStart, $thirtyDaysEnd])
            ->groupBy(DB::raw("1")) // Refer to the SELECT clause's first column (i.e. the aliased day)
            ->orderBy('day', 'desc')
            ->get();

        return response()->json([
            'data' => [
                'locationsFromLast3Days' => LocationResource::collection($locationsFromLast3Days),
                'locationAveragesFrom30DaysBefore' => AggregatedLocationResource::collection($averagedLocations),
            ],
        ]);
    }
}
