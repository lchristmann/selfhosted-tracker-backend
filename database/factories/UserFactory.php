<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            // Save a profile image for the user with 50% chance
            if (fake()->boolean()) {
                $format = Arr::random(['png', 'svg']);
                $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($user->name) .
                    '&background=random' . "&format={$format}" . '&size=256';

                $response = Http::get($avatarUrl);

                if ($response->successful()) {
                    $filename = "user_images/{$user->id}.{$format}";
                    Storage::put($filename, $response->body());
                }
            }
        });
    }
}
