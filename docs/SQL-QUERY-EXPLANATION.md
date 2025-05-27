# SQL Query Explanation

This document is designed to help you (and me in a few months) understand the big SQL at the bottom of the LocationController.

Step by step.

## Step 1

Execute the following in `php artisan tinker`:

```php
$now = Illuminate\Support\Carbon::now('Europe/Berlin')

$twoDaysAgo = $now->copy()->subDays(2)->startOfDay()->getTimestampMs();
// e.g. 1748124000000 meaning May 25, 2025 12:00:00 AM (same as 00:00), in DST (Daylight Saving Time, Germany)
//      (in UTC/GMT this timestamp would equate to May 24, 2025 10:00:00 PM, but we got Europe/Berlin here)

$thirtyDaysStart = Illuminate\Support\Carbon::createFromTimestampMs($twoDaysAgo, 'Europe/Berlin')
            ->copy()
            ->subDays(30)
            ->startOfDay()
            ->getTimestampMs();
// e.g. 1745532000000 meaning April 25, 2025 12:00:00 AM (same as 00:00)
```

Put the values you've got above in the below SQL query:

```postgresql
SELECT
  AVG(latitude),
  AVG(longitude)
FROM locations
WHERE user_id = 1
  AND timestamp BETWEEN 1745532000000 AND 1748124000000;
```

## Step 2

Get a 'YYYY-MM-DD' string from a unix epoch timestamp in milliseconds:

```postgresql
SELECT to_char(timezone('Europe/Berlin', to_timestamp(1746439972000 / 1000)), 'YYYY-MM-DD');
```

Get the unix epoch timestamp **at noon** (12:00) for a given day and timezone (specified by a unix epoch timestamp):

```postgresql
SELECT extract(epoch from (
    date_trunc('day', to_timestamp(AVG(1746439972000) / 1000) AT TIME ZONE 'Europe/Berlin')
    + interval '12 hours'
  ) AT TIME ZONE 'Europe/Berlin') * 1000;
```

Combine the two queries to get unix epoch timestamp at noon for a given day (here '2025-05-25').

The query (1) selects all records for the given day, (2) takes the average timestamp (could be MIN or MAX just as well, doesn't matter),
(3) gets the time at the very start of the day with `date_trunc`, (4) gets the unix epoch timestamp there and
(5) adds 12 hours in milliseconds onto it, so... in the end we get the unix epoch timestamp of the given day at noon (in UTC).

> Here it's just important, that the user 2 _has_ at least one record with timestamp at that day, all else is irrelevant.

```postgresql
SELECT
  (
    extract(epoch from (
      date_trunc('day', to_timestamp(AVG(timestamp) / 1000) AT TIME ZONE 'Europe/Berlin')
      + interval '12 hours'
    ) AT TIME ZONE 'Europe/Berlin') * 1000
  ) AS timestamp_noon_ms
FROM locations
WHERE user_id = 2
  AND to_char(timezone('Europe/Berlin', to_timestamp(timestamp / 1000)), 'YYYY-MM-DD') = '2025-05-25';
```

## Step 3

Now the below query gets the average locations of the user with id 2 for that 30 day time span.

It's basically the same as what's in the `LocationController.php`'s code, but plain PostgreSQL.

```postgresql
SELECT
  to_char(timezone('Europe/Berlin', to_timestamp(timestamp / 1000)), 'YYYY-MM-DD') AS day,
  AVG(latitude) AS average_latitude,
  AVG(longitude) AS average_longitude,
  (
    extract(epoch from (
      date_trunc('day', to_timestamp(AVG(timestamp) / 1000) AT TIME ZONE 'Europe/Berlin')
      + interval '12 hours'
    ) AT TIME ZONE 'Europe/Berlin') * 1000
  ) AS timestamp_noon_ms
FROM locations
WHERE user_id = 2
  AND timestamp BETWEEN 1745532000000 AND 1748124000000
GROUP BY day
ORDER BY day DESC;
```
