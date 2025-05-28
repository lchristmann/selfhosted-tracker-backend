<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DeleteUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete {id : The id of the user to be deleted} {name : The name of the user to be deleted}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a user by id and name (both must match for safety)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $id = (int) $this->argument('id');
        $name = $this->argument('name');

        $user = User::where('id', $id)->where('name', $name)->first();

        if (!$user) {
            $this->error("No user found with id {$id} and name \"{$name}\".");
            return;
        }

        $locationCount = $user->locations()->count();
        $imagePresentText = $user->hasImage() ? ' and their image file' : '';
        $commaOrAnd = $imagePresentText ? ',': ' and';
        if (!$this->confirm("Are you sure you want to delete user \"{$user->name}\" with id {$user->id}{$commaOrAnd} {$locationCount} location(s)$imagePresentText?")) {
            $this->info('Cancelled. No action taken.');
            return;
        }

        // Delete user image if present
        foreach (User::IMAGE_EXTENSIONS as $ext) {
            $path = "user_images/{$user->id}.{$ext}";
            if (Storage::exists($path)) {
                Storage::delete($path);
            }
        }

        $user->delete();
        $this->info("User \"{$name}\" with id {$id} deleted.");
    }
}
