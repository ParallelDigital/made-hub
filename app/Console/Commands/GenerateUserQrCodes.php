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
    protected $signature = 'users:generate-qr-codes {--all : Regenerate QR codes for all users} {--dry : Dry run; do not write changes}';

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
        $all = (bool)$this->option('all');
        $dry = (bool)$this->option('dry');

        $this->info(($all ? 'Regenerating' : 'Generating missing') . ' QR codes for users...' . ($dry ? ' (dry run)' : ''));

        $query = \App\Models\User::query();
        if (!$all) {
            $query->whereNull('qr_code');
        }

        $total = (clone $query)->count();
        $progressBar = $this->output->createProgressBar($total);
        $progressBar->start();

        $updated = 0;
        $query->orderBy('id')->chunk(200, function ($chunk) use (&$progressBar, &$updated, $dry) {
            foreach ($chunk as $user) {
                $qrCode = $this->generateUniqueQrCode();
                if (!$dry) {
                    $user->forceFill(['qr_code' => $qrCode])->save();
                }
                $updated++;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine();
        $this->info("QR codes " . ($dry ? 'would be ' : '') . "set for {$updated} user(s).");
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
