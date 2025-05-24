<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = collect([
            User::factory()->count(2)->hasLocations(100)->create(),
            User::factory()->hasLocations(50)->create(),
            User::factory()->count(2)->create()
        ])->flatten();

        $lines = $users->map(function (User $user) {
            $token = $user->createToken('basic-token');
            return "{$user->id} | {$user->name} | {$token->plainTextToken}";
        });

        file_put_contents(base_path('sanctum_tokens.txt'), $lines->implode("\n"));
    }
}
