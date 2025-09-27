<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class EnsureUserQrCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qr:ensure-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ensure all users have QR codes and fix any broken backup links';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking users for missing QR codes...');
        
        $usersWithoutQr = User::whereNull('qr_code')->get();
        
        if ($usersWithoutQr->count() === 0) {
            $this->info('✅ All users already have QR codes');
        } else {
            $this->warn("Found {$usersWithoutQr->count()} users without QR codes");
            
            $bar = $this->output->createProgressBar($usersWithoutQr->count());
            $bar->start();
            
            foreach ($usersWithoutQr as $user) {
                $qrCode = 'QR' . strtoupper(substr(md5($user->id . $user->email . time()), 0, 8));
                $user->update(['qr_code' => $qrCode]);
                $bar->advance();
            }
            
            $bar->finish();
            $this->line('');
            $this->info("✅ Generated QR codes for {$usersWithoutQr->count()} users");
        }
        
        // Test QR URL generation for a few users
        $this->line('');
        $this->info('Testing QR URL generation...');
        
        $testUsers = User::whereNotNull('qr_code')->take(3)->get();
        
        foreach ($testUsers as $user) {
            try {
                $userQrUrl = \Illuminate\Support\Facades\URL::signedRoute('user.checkin', [
                    'user' => $user->id,
                    'qr_code' => $user->qr_code,
                ]);
                
                $this->line("✅ User {$user->id}: QR code {$user->qr_code} - URL generated successfully");
                
            } catch (\Exception $e) {
                $this->error("❌ User {$user->id}: Failed to generate URL - " . $e->getMessage());
            }
        }
        
        $this->line('');
        $this->info('QR code check complete!');
        
        return 0;
    }
}
