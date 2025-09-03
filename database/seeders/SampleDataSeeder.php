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
        $instructor1 = \App\Models\Instructor::updateOrCreate(['email' => 'sarah@maderunning.com'], [
            'name' => 'Sarah Johnson',
            'phone' => '+1 (555) 123-4567',
            'photo' => 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?w=400&h=400&fit=crop&crop=face',
            'active' => true,
        ]);

        $instructor2 = \App\Models\Instructor::updateOrCreate(['email' => 'mike@maderunning.com'], [
            'name' => 'Mike Chen',
            'phone' => '+1 (555) 234-5678',
            'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop&crop=face',
            'active' => true,
        ]);

        $instructor3 = \App\Models\Instructor::updateOrCreate(['email' => 'emma@maderunning.com'], [
            'name' => 'Emma Rodriguez',
            'phone' => '+1 (555) 345-6789',
            'photo' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&h=400&fit=crop&crop=face',
            'active' => true,
        ]);

        // Create sample classes for today and this week
        $today = now()->format('Y-m-d');
        $tomorrow = now()->addDay()->format('Y-m-d');

        \App\Models\FitnessClass::create([
            'name' => 'Full Body (Lower Focus)',
            'description' => 'High-intensity interval training focusing on lower body',
            'class_date' => $today,
            'max_spots' => 20,
            'price' => 25.00,
            'instructor_id' => $instructor1->id,
            'start_time' => '06:00',
            'end_time' => '07:00',
            'active' => true,
        ]);

        \App\Models\FitnessClass::create([
            'name' => 'Full Body (Lower Focus)',
            'description' => 'High-intensity interval training focusing on lower body',
            'class_date' => $today,
            'max_spots' => 20,
            'price' => 25.00,
            'instructor_id' => $instructor1->id,
            'start_time' => '07:10',
            'end_time' => '08:10',
            'active' => true,
        ]);

        \App\Models\FitnessClass::create([
            'name' => 'Full Body (Lower Focus)',
            'description' => 'High-intensity interval training focusing on lower body',
            'class_date' => $today,
            'max_spots' => 20,
            'price' => 25.00,
            'instructor_id' => $instructor1->id,
            'start_time' => '08:20',
            'end_time' => '09:20',
            'active' => true,
        ]);

        \App\Models\FitnessClass::create([
            'name' => 'Strength & Conditioning',
            'description' => 'Build strength and improve your conditioning',
            'class_date' => $tomorrow,
            'max_spots' => 15,
            'price' => 30.00,
            'instructor_id' => $instructor2->id,
            'start_time' => '18:00',
            'end_time' => '19:00',
            'active' => true,
        ]);

        // Create sample memberships
        \App\Models\Membership::create([
            'name' => 'Basic Monthly',
            'description' => 'Perfect for beginners. Includes 8 classes per month with access to all basic facilities.',
            'price' => 49.99,
            'duration_days' => 30,
            'class_credits' => 8,
            'unlimited' => false,
            'active' => true,
        ]);

        \App\Models\Membership::create([
            'name' => 'Premium Monthly',
            'description' => 'Our most popular plan. Unlimited classes, priority booking, and access to premium facilities.',
            'price' => 89.99,
            'duration_days' => 30,
            'class_credits' => null,
            'unlimited' => true,
            'active' => true,
        ]);

        \App\Models\Membership::create([
            'name' => 'Annual Premium',
            'description' => 'Best value! Unlimited classes for a full year with 2 months free. Includes personal training sessions.',
            'price' => 899.99,
            'duration_days' => 365,
            'class_credits' => null,
            'unlimited' => true,
            'active' => true,
        ]);

        \App\Models\Membership::create([
            'name' => 'Student Plan',
            'description' => 'Special discounted rate for students. 12 classes per month with flexible scheduling.',
            'price' => 35.99,
            'duration_days' => 30,
            'class_credits' => 12,
            'unlimited' => false,
            'active' => true,
        ]);

        // Create sample pricing tiers
        \App\Models\PricingTier::create([
            'name' => 'Early Bird Class Special',
            'description' => 'Book classes before 6 AM and save 20%',
            'type' => 'class',
            'base_price' => 25.00,
            'discount_percentage' => 20.00,
            'final_price' => 20.00,
            'valid_from' => now(),
            'valid_until' => now()->addMonths(3),
            'min_quantity' => 1,
            'max_quantity' => 5,
            'active' => true,
        ]);

        \App\Models\PricingTier::create([
            'name' => 'Student Membership Discount',
            'description' => 'Special pricing for students with valid ID',
            'type' => 'membership',
            'base_price' => 89.99,
            'discount_percentage' => 30.00,
            'final_price' => 62.99,
            'min_quantity' => 1,
            'active' => true,
        ]);

        \App\Models\PricingTier::create([
            'name' => 'Class Bundle Package',
            'description' => '10 classes for the price of 8 - great value!',
            'type' => 'package',
            'base_price' => 250.00,
            'discount_percentage' => 20.00,
            'final_price' => 200.00,
            'min_quantity' => 1,
            'max_quantity' => 2,
            'active' => true,
        ]);

        \App\Models\PricingTier::create([
            'name' => 'Black Friday Special',
            'description' => 'Limited time offer - 50% off all memberships',
            'type' => 'membership',
            'base_price' => 89.99,
            'discount_percentage' => 50.00,
            'final_price' => 44.99,
            'valid_from' => now()->addMonths(2),
            'valid_until' => now()->addMonths(2)->addDays(7),
            'min_quantity' => 1,
            'active' => true,
        ]);

        // Create sample users
        $sampleUsers = [
            ['name' => 'John Smith', 'email' => 'john.smith@example.com'],
            ['name' => 'Emma Wilson', 'email' => 'emma.wilson@example.com'],
            ['name' => 'David Brown', 'email' => 'david.brown@example.com'],
            ['name' => 'Sophie Taylor', 'email' => 'sophie.taylor@example.com'],
            ['name' => 'Michael Davis', 'email' => 'michael.davis@example.com'],
            ['name' => 'John Smith', 'email' => 'john@example.com'],
            ['name' => 'Sarah Johnson', 'email' => 'sarah@example.com'],
            ['name' => 'Mike Wilson', 'email' => 'mike@example.com'],
            ['name' => 'Emma Davis', 'email' => 'emma@example.com'],
            ['name' => 'David Brown', 'email' => 'david-2@example.com'], // Renamed to avoid duplicate
            ['name' => 'Lisa Anderson', 'email' => 'lisa@example.com'],
            ['name' => 'Tom Garcia', 'email' => 'tom@example.com'],
            ['name' => 'Rachel White', 'email' => 'rachel@example.com'],
        ];

        foreach ($sampleUsers as $userData) {
            \App\Models\User::updateOrCreate(['email' => $userData['email']], [
                'name' => $userData['name'],
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
            ]);
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
