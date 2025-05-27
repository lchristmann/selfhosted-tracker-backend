<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class PruneOrphanedSanctumTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sanctum:prune-orphaned-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete Sanctum tokens for users that no longer exist';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $deletedCount = DB::table('personal_access_tokens')
            ->whereNotExists(function (Builder $query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'personal_access_tokens.tokenable_id')
                    ->where('personal_access_tokens.tokenable_type', '=', 'App\\Models\\User');
            })
            ->delete();

        $this->info("Deleted $deletedCount orphaned Sanctum token(s).");
    }
}
