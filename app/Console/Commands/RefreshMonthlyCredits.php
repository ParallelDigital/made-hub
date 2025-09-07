<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class RefreshMonthlyCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:refresh-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh monthly credits for users with active memberships';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly credit refresh...');
        
        $users = User::whereNotNull('membership_id')
            ->whereNotNull('membership_start_date')
            ->where(function ($query) {
                $query->whereNull('membership_end_date')
                      ->orWhere('membership_end_date', '>=', now());
            })
            ->with('membership')
            ->get();

        $refreshedCount = 0;
        
        foreach ($users as $user) {
            if ($user->hasActiveMembership()) {
                $user->refreshMonthlyCreditsIfNeeded();
                $refreshedCount++;
            }
        }

        $this->info("Monthly credits refreshed for {$refreshedCount} users.");
        
        return 0;
    }
}
