<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()
            ->count(2)
            ->hasLocations(100)
            ->create();

        User::factory()
            ->hasLocations(50)
            ->create();

        User::factory()
            ->count(2)
            ->create();
    }
}
