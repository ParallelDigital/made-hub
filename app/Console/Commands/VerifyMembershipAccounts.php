<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class VerifyMembershipAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:verify-accounts {--report-only : Only report issues without fixing them}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify that all memberships have corresponding user accounts with proper roles';

    /**
     * Execute the console command.
     */
     public function handle()
     {
         $this->info('🔍 Verifying all memberships have proper user accounts...');

         $reportOnly = $this->option('report-only');
         $issues = [];
         $fixed = 0;

         // Get all users with memberships (subscription holders)
         $membershipUsers = User::whereNotNull('membership_id')->get();
         $this->info("Found {$membershipUsers->count()} users with memberships");

         foreach ($membershipUsers as $user) {
             $userIssues = [];

             // Check if user has proper role
             if (!$user->role || $user->role !== 'member') {
                 $userIssues[] = "Incorrect role: '{$user->role}' (should be 'member')";
                 if (!$reportOnly) {
                     $user->role = 'member';
                     $user->save();
                     $fixed++;
                 }
             }

             // Check if user has name
             if (!$user->name || trim($user->name) === '') {
                 $userIssues[] = 'Missing name';
                 if (!$reportOnly) {
                     $user->name = 'Member';
                     $user->save();
                     $fixed++;
                 }
             }

             // Check if user has email
             if (!$user->email || !filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
                 $userIssues[] = 'Invalid or missing email';
             }

             // Check if user has proper password (not temporary)
             if (str_starts_with($user->password, '$2y$10$temporary_password_')) {
                 $userIssues[] = 'Has temporary password';
             }

             // Check membership data completeness
             if (!$user->membership_start_date) {
                 $userIssues[] = 'Missing membership start date';
                 if (!$reportOnly) {
                     $user->membership_start_date = now()->toDateString();
                     $user->save();
                     $fixed++;
                 }
             }

             // Check credits
             if ($user->monthly_credits === null || $user->monthly_credits < 0) {
                 $userIssues[] = 'Invalid monthly credits';
                 if (!$reportOnly) {
                     $user->monthly_credits = 5;
                     $user->credits_last_refreshed = now()->startOfMonth()->toDateString();
                     $user->save();
                     $fixed++;
                 }
             }

             if (!empty($userIssues)) {
                 $issues[] = [
                     'user' => $user,
                     'issues' => $userIssues
                 ];

                 if ($reportOnly) {
                     $this->warn("❌ User ID {$user->id} ({$user->email}): " . implode(', ', $userIssues));
                 } else {
                     $this->line("✅ Fixed user ID {$user->id} ({$user->email}): " . implode(', ', $userIssues));
                 }
             } else {
                 $this->line("✅ User ID {$user->id} ({$user->email}) is properly configured");
             }
         }

         // Summary
         $this->info("\n📊 Summary:");
         $this->info("Total membership users: {$membershipUsers->count()}");
         $this->info("Users with issues: " . count($issues));

         if (!$reportOnly) {
             $this->info("Issues fixed: {$fixed}");
         }

         if (count($issues) === 0) {
             $this->info("🎉 All membership accounts are properly configured!");
             return Command::SUCCESS;
         } else {
             $this->warn("⚠️  Found " . count($issues) . " membership accounts with issues");
             if ($reportOnly) {
                 $this->info("Run without --report-only to fix these issues");
             }
             return Command::FAILURE;
         }
     }
}
