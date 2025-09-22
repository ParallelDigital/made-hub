<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Membership;
use Illuminate\Console\Command;

class UpdateMemberCredits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:update-credits {--force : Force update all members regardless of last refresh date}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all members have 5 credits that refresh on the 1st of each month';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking memberships and updating class_credits to 5...');

        // First, ensure all memberships have 5 class_credits
        $membershipsUpdated = Membership::where('class_credits', '!=', 5)
            ->orWhereNull('class_credits')
            ->update(['class_credits' => 5]);

        $this->info("✅ Updated {$membershipsUpdated} membership records to have 5 credits");

        // Find all users with active memberships
        $activeMembers = User::whereNotNull('membership_id')
            ->whereNotNull('membership_start_date')
            ->where('membership_start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('membership_end_date')
                      ->orWhere('membership_end_date', '>=', now());
            })
            ->get();

        $this->info("🔍 Found {$activeMembers->count()} active members");

        $updated = 0;
        $force = $this->option('force');

        foreach ($activeMembers as $user) {
            $needsUpdate = false;

            if ($force) {
                $needsUpdate = true;
                $this->line("🔄 Forcing refresh for {$user->email}");
            } else {
                // Check if credits need to be refreshed (1st of month logic)
                $firstOfMonth = now()->startOfMonth()->toDateString();

                if (!$user->credits_last_refreshed || $user->credits_last_refreshed < $firstOfMonth) {
                    $needsUpdate = true;
                }
            }

            if ($needsUpdate) {
                $user->monthly_credits = 5;
                $user->credits_last_refreshed = now()->startOfMonth()->toDateString();
                $user->save();

                $updated++;
                $this->line("✅ Updated {$user->email} - now has 5 credits");
            }
        }

        $this->info("🎉 Successfully updated {$updated} members with 5 monthly credits");
        $this->info("📅 Credits will automatically refresh on the 1st of each month");

        return Command::SUCCESS;
    }
}
