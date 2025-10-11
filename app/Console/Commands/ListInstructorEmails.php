<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Instructor;

class ListInstructorEmails extends Command
{
    protected $signature = 'instructors:list-emails {--json : Output as JSON} {--csv : Output as CSV}';
    protected $description = 'List all instructor emails';

    public function handle()
    {
        $instructors = Instructor::orderBy('id')->get(['id', 'name', 'email', 'phone', 'active']);

        if ($this->option('json')) {
            $this->line(json_encode($instructors->toArray(), JSON_PRETTY_PRINT));
            return 0;
        }

        if ($this->option('csv')) {
            $this->line('ID,Name,Email,Phone,Active');
            foreach ($instructors as $instructor) {
                $this->line(sprintf(
                    '%d,"%s","%s","%s",%s',
                    $instructor->id,
                    $instructor->name,
                    $instructor->email ?: '',
                    $instructor->phone ?: '',
                    $instructor->active ? 'Yes' : 'No'
                ));
            }
            return 0;
        }

        // Default formatted output
        $this->info('═══════════════════════════════════════════════════════════════════════════════════');
        $this->info('ALL INSTRUCTORS - EMAIL ADDRESSES');
        $this->info('═══════════════════════════════════════════════════════════════════════════════════');
        $this->newLine();
        $this->info(sprintf('Total: %d instructors', $instructors->count()));
        $this->newLine();

        $this->table(
            ['ID', 'Name', 'Email', 'Phone', 'Active'],
            $instructors->map(function ($instructor) {
                return [
                    'id' => $instructor->id,
                    'name' => $instructor->name,
                    'email' => $instructor->email ?: 'NO EMAIL',
                    'phone' => $instructor->phone ?: '-',
                    'active' => $instructor->active ? '✓' : '✗',
                ];
            })
        );

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════════════════════════');
        
        // Show email list for easy copying
        $this->newLine();
        $this->comment('Email addresses only (for easy copying):');
        $this->newLine();
        foreach ($instructors->whereNotNull('email') as $instructor) {
            $this->line($instructor->email);
        }

        return 0;
    }
}
