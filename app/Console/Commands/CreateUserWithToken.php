<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class CreateUserWithToken extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {name : The name of the user to be created}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user with a Sanctum token';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $name = $this->argument('name');

        $user = User::create([
            'name' => $name,
        ]);

        $token = $user->createToken('basic-token')->plainTextToken;

        $this->info("User[id: {$user->id}, name: \"{$user->name}\"] created successfully! Sanctum token:");
        $this->line($token);
    }
}
