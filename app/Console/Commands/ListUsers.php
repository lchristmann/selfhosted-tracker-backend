<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list {--name= : A name (or part of it) to perform a case-insensitive search}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List users, optionally search for a name with the --name option';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $nameToSearchFor = $this->option('name');
        $query = User::query();

        if ($nameToSearchFor) $query->where('name', 'ilike', '%' . $nameToSearchFor . '%');

        $users = $query->latest()->get(['id', 'name', 'created_at', 'updated_at']);

        if ($users->isEmpty()) {
            $this->warn('No users found.');
            return;
        }

        // Format for table
        $formatted = $users->map(function (User $user){
           $latestTimestamp = $user->locations()->latest('timestamp')->value('timestamp');
           return [
               'Id' => $user->id,
               'Name' => $user->name,
               'Has Image' => $user->hasImage() ? 'yes' : '',
               'Locations' => $user->locations()->count(),
               'Last Seen' => $latestTimestamp ? Carbon::createFromTimestampMs($latestTimestamp)->format('Y-m-d H:i:s') : '',
               'Created At' => $user->created_at->format('Y-m-d H:i:s'),
               'Updated At' => $user->updated_at->format('Y-m-d H:i:s'),
           ] ;
        });

        $this->table(
            ['id', 'name', 'Has Image', 'Locations', 'Last Seen', 'Created At', 'Updated At'],
            $formatted
        );
    }
}
