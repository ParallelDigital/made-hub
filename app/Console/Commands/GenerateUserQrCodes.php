<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateUserQrCodes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:generate-qr-codes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate unique QR codes for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating QR codes for users...');

        $users = \App\Models\User::whereNull('qr_code')->get();
        $progressBar = $this->output->createProgressBar($users->count());
        $progressBar->start();

        foreach ($users as $user) {
            $qrCode = $this->generateUniqueQrCode();
            $user->update(['qr_code' => $qrCode]);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info('QR codes generated successfully for ' . $users->count() . ' users!');
    }

    /**
     * Generate a unique QR code
     */
    private function generateUniqueQrCode(): string
    {
        do {
            $qrCode = 'QR' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (\App\Models\User::where('qr_code', $qrCode)->exists());

        return $qrCode;
    }
}
