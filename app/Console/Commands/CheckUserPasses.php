<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserPass;
use Illuminate\Console\Command;

class CheckUserPasses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:user-passes {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check pass information for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return 1;
        }
        
        $this->info("User found: {$user->name} (ID: {$user->id})");
        $this->info("Email: {$user->email}");
        $this->line('');
        
        // Check old system
        $this->info('Old System:');
        $this->line("  Credits: " . ($user->credits ?? 0));
        $this->line("  Credits expire: " . ($user->credits_expires_at ? $user->credits_expires_at->format('Y-m-d') : 'None'));
        $this->line("  Unlimited pass expires: " . ($user->unlimited_pass_expires_at ? $user->unlimited_pass_expires_at->format('Y-m-d') : 'None'));
        $this->line('');
        
        // Check new system
        $this->info('New System:');
        try {
            $passes = $user->passes;
            $this->line("  UserPass records: " . $passes->count());
            
            foreach ($passes as $pass) {
                $status = $pass->expires_at && $pass->expires_at->isFuture() ? 'Active' : 'Expired';
                $this->line("    - {$pass->pass_type}: {$pass->credits} credits, expires {$pass->expires_at->format('Y-m-d')}, source: {$pass->source} ({$status})");
            }
            
            $totalCredits = $user->getNonMemberAvailableCredits();
            $this->line("  Total available credits: {$totalCredits}");
            
        } catch (\Exception $e) {
            $this->error("Error accessing new system: " . $e->getMessage());
        }
        
        $this->line('');
        
        // Check if user would appear in admin query
        $this->info('Admin Query Test:');
        $query = User::query()
            ->where(function ($q) {
                $q->whereHas('passes');
            })
            ->orWhere(function ($q) {
                $q->where('credits', '>', 0)->orWhereNotNull('unlimited_pass_expires_at');
            });
            
        $found = $query->where('id', $user->id)->exists();
        $this->line("  Would appear in admin list: " . ($found ? 'YES' : 'NO'));
        
        return 0;
    }
}
