<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ImportUsersFromCsv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:import-csv {file=user.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from CSV file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = base_path($this->argument('file'));
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $this->info("Importing users from: {$filePath}");
        
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);
        
        $imported = 0;
        $skipped = 0;
        
        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($headers, $row);
            
            // Skip if email is empty
            if (empty($data['user_email'])) {
                $skipped++;
                continue;
            }
            
            // Check if user already exists
            if (User::where('email', $data['user_email'])->exists()) {
                $skipped++;
                continue;
            }
            
            // Create user
            User::create([
                'user_login' => $data['user_login'] ?? null,
                'email' => $data['user_email'],
                'password' => $data['user_pass'] ?? 'default_password', // Preserve original WordPress hash exactly as is
                'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')) ?: ($data['display_name'] ?? $data['user_login'] ?? 'User'),
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'nickname' => $data['nickname'] ?? null,
                'display_name' => $data['display_name'] ?? null,
                'role' => $data['role'] ?? 'subscriber',
                'credits' => 0,
            ]);
            
            $imported++;
        }
        
        fclose($handle);
        
        $this->info("Import completed!");
        $this->info("Imported: {$imported} users");
        $this->info("Skipped: {$skipped} users");
        
        return 0;
    }
}
