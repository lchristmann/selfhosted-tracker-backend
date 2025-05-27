<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Storage::deleteDirectory('user_images'); // delete old user images
        File::put(base_path('sanctum_tokens.txt'), ''); // create empty file

        $users = collect([
            User::factory()->count(2)->hasLocations(200)->create(),
            User::factory()->count(2)->create()
        ])->flatten();

        $users->each(function (User $user) {
            $token = $user->createToken('basic-token')->plainTextToken;
            $line = "{$user->id} | {$user->name} | {$token}" . PHP_EOL;
            File::append(base_path('sanctum_tokens.txt'), $line);
        });
    }
}
