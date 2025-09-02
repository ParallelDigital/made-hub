<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample instructors
        $instructors = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@maderunning.com',
                'phone' => '+1 (555) 123-4567',
                'bio' => 'Certified personal trainer with 8+ years of experience in HIIT and strength training. Former competitive athlete passionate about helping others reach their fitness goals.',
                'specialties' => 'HIIT, Strength Training, Functional Fitness',
                'photo' => 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=400&h=400&fit=crop&crop=face',
                'active' => true,
            ],
            [
                'name' => 'Mike Chen',
                'email' => 'mike@maderunning.com',
                'phone' => '+1 (555) 234-5678',
                'bio' => 'Yoga instructor and mindfulness coach with expertise in both traditional and power yoga. Believes in the mind-body connection for optimal wellness.',
                'specialties' => 'Yoga, Pilates, Meditation',
                'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop&crop=face',
                'active' => true,
            ],
            [
                'name' => 'Emma Rodriguez',
                'email' => 'emma@maderunning.com',
                'phone' => '+1 (555) 345-6789',
                'bio' => 'Former professional boxer turned fitness instructor. Specializes in high-energy cardio workouts and boxing techniques for all fitness levels.',
                'specialties' => 'Boxing, Cardio, HIIT',
                'photo' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&h=400&fit=crop&crop=face',
                'active' => true,
            ],
        ];

        foreach ($instructors as $instructorData) {
            $instructor = \App\Models\Instructor::create($instructorData);

            // Create sample classes for each instructor
            $classes = [
                [
                    'name' => 'Morning HIIT Blast',
                    'description' => 'High-intensity interval training to kickstart your day with energy and burn calories efficiently.',
                    'duration' => 45,
                    'max_spots' => 20,
                    'price' => 25.00,
                    'start_time' => '07:00',
                    'end_time' => '07:45',
                    'active' => true,
                ],
                [
                    'name' => 'Strength & Power',
                    'description' => 'Build lean muscle and increase strength with compound movements and progressive overload.',
                    'duration' => 60,
                    'max_spots' => 15,
                    'price' => 30.00,
                    'start_time' => '18:00',
                    'end_time' => '19:00',
                    'active' => true,
                ],
            ];

            foreach ($classes as $classData) {
                $classData['instructor_id'] = $instructor->id;
                \App\Models\FitnessClass::create($classData);
            }
        }

        // Create some sample bookings
        $users = \App\Models\User::where('role', 'user')->get();
        $classes = \App\Models\FitnessClass::all();

        if ($users->count() > 0 && $classes->count() > 0) {
            for ($i = 0; $i < 15; $i++) {
                \App\Models\Booking::create([
                    'user_id' => $users->random()->id,
                    'fitness_class_id' => $classes->random()->id,
                    'status' => collect(['confirmed', 'confirmed', 'confirmed', 'cancelled', 'waitlist'])->random(),
                    'booked_at' => now()->subDays(rand(1, 30)),
                ]);
            }
        }
    }
}
