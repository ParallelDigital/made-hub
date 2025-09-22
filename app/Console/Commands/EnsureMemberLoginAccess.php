<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;

class EnsureMemberLoginAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'members:ensure-login-access {--force-reset : Force send password reset emails to all members}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all members with active subscriptions have login access';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Checking all members with active subscriptions for login access...');

        // Find all users with active memberships
        $activeMembers = User::whereNotNull('membership_id')
            ->whereNotNull('membership_start_date')
            ->where('membership_start_date', '<=', now())
            ->where(function ($query) {
                $query->whereNull('membership_end_date')
                      ->orWhere('membership_end_date', '>=', now());
            })
            ->get();

        $this->info("Found {$activeMembers->count()} active members");

        $processed = 0;
        $passwordResetsSent = 0;
        $forceReset = $this->option('force-reset');

        foreach ($activeMembers as $user) {
            $needsPasswordReset = false;

            // Check if user has a temporary password
            if (str_starts_with($user->password, '$2y$10$temporary_password_')) {
                $needsPasswordReset = true;
                $this->line("❌ User {$user->email} has temporary password - needs reset");
            } elseif (!$user->password) {
                // User has no password at all
                $needsPasswordReset = true;
                $this->line("❌ User {$user->email} has no password - needs reset");
            } elseif ($forceReset) {
                $needsPasswordReset = true;
                $this->line("🔄 Force resetting password for {$user->email}");
            }

            if ($needsPasswordReset) {
                try {
                    $status = Password::sendResetLink(['email' => $user->email]);
                    if ($status === Password::RESET_LINK_SENT) {
                        $passwordResetsSent++;
                        $this->line("✅ Password reset email sent to {$user->email}");
                    } else {
                        $this->error("❌ Failed to send password reset to {$user->email}: {$status}");
                    }
                } catch (\Throwable $e) {
                    $this->error("❌ Error sending password reset to {$user->email}: {$e->getMessage()}");
                    Log::error('Failed to send password reset for member', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                $this->line("✅ User {$user->email} has proper login access");
            }

            $processed++;
        }

        $this->info("\n🎉 Processed {$processed} active members");
        $this->info("📧 Sent {$passwordResetsSent} password reset emails");
        $this->info("🔒 All members now have or will have login access");

        return Command::SUCCESS;
    }
}
