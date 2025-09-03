<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InstructorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::updateOrCreate(['email' => 'instructor@made.com'], [
            'name' => 'Instructor User',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now(),
        ]);

        \App\Models\User::updateOrCreate(['email' => 'chrissy@made.com'], [
            'name' => 'Chrissy',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role' => 'instructor',
            'email_verified_at' => now(),
        ]);
    }
}
