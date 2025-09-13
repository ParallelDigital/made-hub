<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassType;

class ClassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $classTypes = [
            [
                'name' => 'Yoga',
                'description' => 'A group of physical, mental, and spiritual practices originating in ancient India.',
                'duration' => 60,
                'color' => '#3b82f6', // blue-500
                'active' => true
            ],
            [
                'name' => 'Pilates',
                'description' => 'A physical fitness system developed in the early 20th century by Joseph Pilates.',
                'duration' => 45,
                'color' => '#10b981', // emerald-500
                'active' => true
            ],
            [
                'name' => 'HIIT',
                'description' => 'High-Intensity Interval Training for cardiovascular fitness and fat burning.',
                'duration' => 30,
                'color' => '#ef4444', // red-500
                'active' => true
            ],
            [
                'name' => 'Zumba',
                'description' => 'A fitness program that combines Latin and international music with dance moves.',
                'duration' => 60,
                'color' => '#8b5cf6', // violet-500
                'active' => true
            ],
            [
                'name' => 'Spin',
                'description' => 'Indoor cycling class focusing on endurance, strength, and high-intensity intervals.',
                'duration' => 45,
                'color' => '#f59e0b', // amber-500
                'active' => true
            ]
        ];

        foreach ($classTypes as $classType) {
            ClassType::updateOrCreate(
                ['name' => $classType['name']],
                $classType
            );
        }
    }
}
