<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ResetMonthlyCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credits:reset-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset monthly credits for all users to 5';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $firstOfMonth = now()->startOfMonth()->toDateString();
            $count = 0;
            
            // Process users in chunks to avoid memory issues
            User::where('role', 'member')
                ->where(function($query) use ($firstOfMonth) {
                    $query->whereNull('credits_last_refreshed')
                          ->orWhere('credits_last_refreshed', '<', $firstOfMonth);
                })
                ->chunk(200, function($users) use (&$count, $firstOfMonth) {
                    foreach ($users as $user) {
                        // This will trigger the refreshMonthlyCreditsIfNeeded method
                        // which will update credits if needed
                        $user->refreshMonthlyCreditsIfNeeded();
                        $count++;
                    }
                });
                
            $this->info("Successfully refreshed credits for {$count} users.");
            Log::info("Monthly credits refresh completed for {$count} users");
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error resetting credits: " . $e->getMessage());
            Log::error("Error resetting monthly credits: " . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
