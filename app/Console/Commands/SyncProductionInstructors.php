<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Instructor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SyncProductionInstructors extends Command
{
    protected $signature = 'sync:production-instructors {--dry-run : Show what would be synced without making changes}';
    protected $description = 'Sync instructors from production database to local database';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $this->info('🔄 Syncing instructors from production database...');
        if ($dryRun) {
            $this->warn('DRY RUN MODE - No changes will be made');
        }
        $this->newLine();

        try {
            // Fetch instructors from production database
            $productionInstructors = DB::connection('production')
                ->table('instructors')
                ->orderBy('id')
                ->get();

            $this->info("Found {$productionInstructors->count()} instructors in production database");
            $this->newLine();

            $synced = 0;
            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($productionInstructors as $prodInstructor) {
                $this->line("Processing: ID {$prodInstructor->id} - {$prodInstructor->name} ({$prodInstructor->email})");

                // Check if instructor already exists locally
                $localInstructor = Instructor::find($prodInstructor->id);

                if ($localInstructor) {
                    // Check if update is needed
                    $needsUpdate = $localInstructor->name !== $prodInstructor->name ||
                                   $localInstructor->email !== $prodInstructor->email ||
                                   $localInstructor->phone !== $prodInstructor->phone ||
                                   $localInstructor->active !== (bool)$prodInstructor->active;

                    if ($needsUpdate) {
                        if (!$dryRun) {
                            $localInstructor->update([
                                'name' => $prodInstructor->name,
                                'email' => $prodInstructor->email,
                                'phone' => $prodInstructor->phone,
                                'photo' => $prodInstructor->photo,
                                'active' => (bool)$prodInstructor->active,
                            ]);

                            // Sync or create associated user
                            $this->syncInstructorUser($prodInstructor);
                        }
                        $this->info("  ✓ Updated instructor");
                        $updated++;
                    } else {
                        $this->comment("  - Already in sync");
                        $skipped++;
                    }
                } else {
                    // Create new instructor
                    if (!$dryRun) {
                        Instructor::create([
                            'id' => $prodInstructor->id,
                            'name' => $prodInstructor->name,
                            'email' => $prodInstructor->email,
                            'phone' => $prodInstructor->phone,
                            'photo' => $prodInstructor->photo,
                            'active' => (bool)$prodInstructor->active,
                            'created_at' => $prodInstructor->created_at,
                            'updated_at' => $prodInstructor->updated_at,
                        ]);

                        // Create associated user
                        $this->syncInstructorUser($prodInstructor);
                    }
                    $this->info("  ✓ Created new instructor");
                    $created++;
                }

                $synced++;
            }

            $this->newLine();
            $this->info('═══════════════════════════════════════');
            $this->info("✓ Sync completed!");
            $this->info("Total processed: {$synced}");
            $this->info("Created: {$created}");
            $this->info("Updated: {$updated}");
            $this->info("Skipped (already in sync): {$skipped}");
            $this->info('═══════════════════════════════════════');

            if ($dryRun) {
                $this->newLine();
                $this->warn('This was a DRY RUN. Run without --dry-run to apply changes.');
            }

            return 0;

        } catch (\Exception $e) {
            $this->error('Error connecting to production database: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Make sure you have set the following in your .env file:');
            $this->line('PRODUCTION_DB_HOST=your_production_host');
            $this->line('PRODUCTION_DB_PORT=3306');
            $this->line('PRODUCTION_DB_DATABASE=your_production_database');
            $this->line('PRODUCTION_DB_USERNAME=your_production_username');
            $this->line('PRODUCTION_DB_PASSWORD=your_production_password');
            return 1;
        }
    }

    /**
     * Sync or create user account for instructor
     */
    private function syncInstructorUser($prodInstructor)
    {
        $user = User::where('email', $prodInstructor->email)->first();

        if (!$user) {
            // Create user with temporary password
            User::create([
                'name' => $prodInstructor->name,
                'email' => $prodInstructor->email,
                'password' => Hash::make('temporary_' . time()),
                'role' => 'instructor',
            ]);
        } else {
            // Update existing user
            $user->update([
                'name' => $prodInstructor->name,
                'role' => 'instructor',
            ]);
        }
    }
}
