<?php

namespace App\Console\Commands;

use App\Models\FitnessClass;
use Carbon\Carbon;
use Illuminate\Console\Command;

class DebugClassTiming extends Command
{
    protected $signature = 'debug:class-timing {class_id?}';
    protected $description = 'Debug class timing calculations';

    public function handle()
    {
        $classId = $this->argument('class_id');
        
        if ($classId) {
            $class = FitnessClass::find($classId);
            if (!$class) {
                $this->error("Class with ID {$classId} not found");
                return;
            }
            $classes = collect([$class]);
        } else {
            // Get next few upcoming classes
            $classes = FitnessClass::where('class_date', '>=', now()->subDay())
                ->orderBy('class_date')
                ->orderBy('start_time')
                ->limit(5)
                ->get();
        }

        $this->info('Current server time: ' . now());
        $this->info('Current London time: ' . Carbon::now('Europe/London'));
        $this->line('');

        foreach ($classes as $class) {
            $this->info("Class: {$class->name}");
            $this->info("Stored class_date: {$class->class_date} (type: " . get_class($class->class_date) . ")");
            $this->info("Start time: {$class->start_time}");
            
            // OLD METHOD (buggy)
            $oldClassStart = Carbon::parse($class->class_date->format('Y-m-d') . ' ' . $class->start_time, 'Europe/London');
            $this->warn("OLD calculation: {$oldClassStart}");
            $this->warn("OLD isPast: " . ($oldClassStart->lessThan(Carbon::now('Europe/London')) ? 'YES' : 'NO'));
            
            // NEW METHOD (fixed)
            $tz = 'Europe/London';
            $classDate = Carbon::parse($class->class_date)->setTimezone($tz)->format('Y-m-d');
            $newClassStart = Carbon::parse($classDate . ' ' . $class->start_time, $tz);
            $this->info("NEW calculation: {$newClassStart}");
            $this->info("NEW isPast: " . ($newClassStart->lessThan(Carbon::now($tz)) ? 'YES' : 'NO'));
            
            $this->line('---');
        }
    }
}
