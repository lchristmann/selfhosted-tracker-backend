# API Documentation <!-- omit in toc -->

## Table of Contents <!-- omit in toc -->

- [General Usage](#general-usage)
- [Endpoints](#endpoints)
  - [1. GET /me | GET /users/{user}](#1-get-me--get-usersuser)
  - [2. GET /users](#2-get-users)
  - [3. GET /me/image | GET /users/{user}/image](#3-get-meimage--get-usersuserimage)
  - [4. GET /me/locations | GET /users/{user}/locations](#4-get-melocations--get-usersuserlocations)
    - [4.1. Example with `tz` parameter](#41-example-with-tz-parameter)
    - [4.2. Example with `date` parameter](#42-example-with-date-parameter)
    - [4.3. Example with `from` and `to` parameters](#43-example-with-from-and-to-parameters)
  - [5. PUT /me | PATCH /me](#5-put-me--patch-me)
  - [6. POST /me/locations](#6-post-melocations)

## General Usage

When making HTTP requests to the API, **send those two headers:**

- Accept: application/json
- Authorization: Bearer <your_api_token>

The bearer token is the Laravel Sanctum personal access token, which only the
administrator of the Quokka Tracker Backend sees once, when he creates a new user.

## Endpoints

|   | Method | Endpoint                | Description                      | Resource |
|---|--------|-------------------------|----------------------------------|----------|
| 1 | GET    | /me                     | fetch **my** profile             | User     |
| 5 | PUT    | /me                     | update **all my** profile        | User     |
| 5 | PATCH  | /me                     | update **some of my** profile    | User     |
| 3 | GET    | /me/image               | fetch **my** profile image       | User     |
| 4 | GET    | /me/locations           | fetch **my** locations           | Location |
| 6 | POST   | /me/locations           | upload locations **of mine**     | Location |
|   |        |                         |                                  |          |
| 2 | GET    | /users                  | fetch **all** profiles           | User     |
| 1 | GET    | /users/{user}           | fetch **a user's** profile       | User     |
| 3 | GET    | /users/{user}/image     | fetch **a user's** profile image | User     |
| 4 | GET    | /users/{user}/locations | fetch **a user's** locations     | Location |

### 1. GET /me | GET /users/{user}

Returns a user's data.

The user is identified either by bearer token (/me) or by id passed in the URL (e.g. /users/2).

Example response:

```json
{
    "data": {
        "id": 2,
        "name": "Octavia Cassin",
        "hasImage": true
    }
}
```

### 2. GET /users

Returns all users' data.

Example response:

```json
{
    "data": [
        {
            "id": 1,
            "name": "Ramona Trantow IV",
            "hasImage": true
        },
        {
            "id": 2,
            "name": "Octavia Cassin",
            "hasImage": true
        },
        {
            "id": 3,
            "name": "Ramon Mayert V",
            "hasImage": false
        }
    ]
}
```

### 3. GET /me/image | GET /users/{user}/image

Returns a user's image. If none can be found, returns a 404 Not Found response.

The user is identified either by bearer token (/me) or by id passed in the URL (e.g. /users/2).

Example response (`Content-Type` is `image/png`):

![User Image Example](user-image-example.png)

### 4. GET /me/locations | GET /users/{user}/locations

Returns a user's locations.

The user is identified either by bearer token (/me) or by id passed in the URL (e.g. /users/2).

Parameters:

| Parameter | Default | Example       | Description       | Required | Recommended     |
|-----------|---------|---------------|-------------------|----------|-----------------|
| tz        | UTC     | Europe/Berlin | Time zone         | no       | yes, absolutely |
| date      |         | 2025-05-25    |                   | no       |                 |
| from      |         | 2025-05-20    | from start of day | no       |                 |
| to        |         | 2025-05-25    | until end of day  | no       |                 |

#### 4.1. Example with `tz` parameter

Using the `tz` parameter makes time zone specific day boundaries be respected.

If `tz` is not passed, then `UTC` is used, meaning for a German user during Daylight Saving Time (DST)
his locations would be grouped in days always ranging from 22:00 of the last day to 22:00 on the current day.

For the full list of available time zones, run `php -r "print_r(DateTimeZone::listIdentifiers());"`.

Example request:

```text
/me/locations?tz=Europe%2FBerlin
```

Example response:

```json
{
    "data": {
        "locationsFromLast3Days": [
            {
                "id": 238,
                "latitude": "-59.4746740",
                "longitude": "-32.0863810",
                "timestamp": 1748374614000
            },
            ...
            {
                "id": 226,
                "latitude": "-12.9233280",
                "longitude": "139.8164670",
                "timestamp": 1748224080000
            }
        ],
        "locationAveragesFrom30DaysBefore": [
            {
                "day": "2025-05-25",
                "latitude": -21.350779,
                "longitude": -109.590242,
                "timestamp": 1748167200000
            },
            {
                "day": "2025-05-24",
                "latitude": 48.460507,
                "longitude": -28.418969,
                "timestamp": 1748080800000
            },
            ...
            {
                "day": "2025-04-26",
                "latitude": -43.0189515,
                "longitude": -72.918887,
                "timestamp": 1745661600000
            }
        ]
    }
}
````

#### 4.2. Example with `date` parameter

Example request:

```text
/me/locations?tz=Europe%2FBerlin&date=2025-05-25
```

Example response:

```json
{
    "data": [
        {
            "id": 295,
            "latitude": "-30.1462990",
            "longitude": "-131.5294750",
            "timestamp": 1748191802000
        },
        {
            "id": 364,
            "latitude": "-85.9465950",
            "longitude": "-62.9418460",
            "timestamp": 1748129352000
        },
        ...
    ]
}
```

#### 4.3. Example with `from` and `to` parameters

**The parameters `from` and `to` don't have to be used together - specifying only one is valid, too.**

- `from`: request locations from a given start date on
- `to`: request locations up until a given end date

Example request:

```text
/me/locations?tz=Europe%2FBerlin&from=2025-05-20&to=2025-05-25
```

Example response:

```json
{
    "data": [
        {
            "id": 295,
            "latitude": "-30.1462990",
            "longitude": "-131.5294750",
            "timestamp": 1748191802000
        },
        {
            "id": 262,
            "latitude": "-44.3994830",
            "longitude": "-70.5958450",
            "timestamp": 1748181422000
        },
        ...
        {
            "id": 343,
            "latitude": "-39.2306340",
            "longitude": "15.2293320",
            "timestamp": 1747707338000
        }
    ]
}
```

### 5. PUT /me | PATCH /me

Updates a user's data.

| Parameter | Type | Restrictions                                                                | 
|-----------|------|-----------------------------------------------------------------------------|
| name      | Text | 128 characters limit                                                        |
| image     | File | jpg, jpeg, png, bmp, gif, webp or svg format, maximum size 2048 KB (= 2 MB) |

If you use `PUT`, you must pass **both parameters**. Use `PATCH` if you want to update only some.

**IMPORTANT: You must make a `POST` request instead here, sending multipart form-data.**

**Specify the method by passing another field `_method` with a value of either `PUT` or `PATCH`**, for the Laravel API to know your intentions. 

> Reason for this is a technical limitation of PHP, [which doesn't parse multipart form-data unless the request method is `POST`](https://stackoverflow.com/a/61768745/20594090).

Example request:

```text
POST /me

name (text field): Octavia Cassin
image (file): identicon.png
_method (text field): PUT
```

Example response:

```json
{
    "data": {
        "id": 2,
        "name": "Octavia Cassin",
        "hasImage": true
    }
}
````

### 6. POST /me/locations

Upload locations for a user.

The user is identified by bearer token (/me).

This endpoint expects an array of location measurements. The array may have any number (even zero) of objects.

| Property  | Description                                | 
|-----------|--------------------------------------------|
| latitude  | A latitude value ranging from -90 to 90    |
| longitude | A longitude value ranging from -180 to 180 |
| timestamp | A unix epoch timestamp in milliseconds     |

> Precision: the latitude and longitude can be passed with any number of decimal places, but at most 7 are stored.

Example request (with a raw JSON request body):

```text
POST /me/locations
```

```json
[
    {
        "latitude": 56.440064,
        "longitude": 147.857621,
        "timestamp": 1746697738000
    },
    {
        "latitude": 19.721379,
        "longitude": -101.028881,
        "timestamp": 1746439972000
    }
]
```

Example response:

```json
{
    "data": [
        {
            "id": 401,
            "latitude": 56.440064,
            "longitude": 147.857621,
            "timestamp": 1746697738000
        },
        {
            "id": 402,
            "latitude": 19.721379,
            "longitude": -101.028881,
            "timestamp": 1746439972000
        }
    ]
}
````
