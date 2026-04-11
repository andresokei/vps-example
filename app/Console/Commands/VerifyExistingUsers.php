<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyExistingUsers extends Command
{
    protected $signature = 'users:verify-existing';
    protected $description = 'Mark all existing users as email verified';

    public function handle(): void
    {
        $count = User::whereNull('email_verified_at')->count();

        if ($count === 0) {
            $this->info('All users are already verified.');
            return;
        }

        $this->info("Found {$count} unverified user(s).");

        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);

        $this->info("Done. {$count} user(s) marked as verified.");
    }
}
