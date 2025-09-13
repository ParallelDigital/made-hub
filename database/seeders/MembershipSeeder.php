<?php

namespace Database\Seeders;

use App\Models\Membership;
use Illuminate\Database\Seeder;

class MembershipSeeder extends Seeder
{
    public function run(): void
    {
        $memberships = [
            [
                'name' => 'Basic Membership',
                'description' => '5 classes per month',
                'price' => 49.99,
                'duration_days' => 30,
                'class_credits' => 5,
                'unlimited' => false,
                'active' => true,
            ],
            [
                'name' => 'Premium Membership',
                'description' => 'Unlimited classes',
                'price' => 99.99,
                'duration_days' => 30,
                'class_credits' => 0,
                'unlimited' => true,
                'active' => true,
            ],
        ];

        foreach ($memberships as $membership) {
            Membership::firstOrCreate(
                ['name' => $membership['name']],
                $membership
            );
        }
    }
}
