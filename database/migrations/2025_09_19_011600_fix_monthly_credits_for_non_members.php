<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $today = now()->toDateString();

        // 1) Set monthly_credits = 0 for all users WITHOUT an active membership
        DB::table('users')
            ->where(function ($q) use ($today) {
                $q->whereNull('membership_id')
                  ->orWhereNull('membership_start_date')
                  ->orWhere('membership_start_date', '>', $today)
                  ->orWhere(function ($q2) use ($today) {
                      $q2->whereNotNull('membership_end_date')
                         ->where('membership_end_date', '<', $today);
                  });
            })
            ->update(['monthly_credits' => 0]);

        // 2) Ensure active members have at least 5 monthly_credits
        DB::table('users')
            ->whereNotNull('membership_id')
            ->whereNotNull('membership_start_date')
            ->where('membership_start_date', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('membership_end_date')
                  ->orWhere('membership_end_date', '>=', $today);
            })
            ->where(function ($q) {
                $q->whereNull('monthly_credits')
                  ->orWhere('monthly_credits', '<', 5);
            })
            ->update(['monthly_credits' => 5]);

        // 3) Cleanup legacy credits that were incorrectly defaulted to 5 for non-members
        //    This avoids showing 5 credits for users without an active membership.
        DB::table('users')
            ->where(function ($q) use ($today) {
                $q->whereNull('membership_id')
                  ->orWhereNull('membership_start_date')
                  ->orWhere('membership_start_date', '>', $today)
                  ->orWhere(function ($q2) use ($today) {
                      $q2->whereNotNull('membership_end_date')
                         ->where('membership_end_date', '<', $today);
                  });
            })
            ->where('credits', '=', 5)
            ->update(['credits' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: we won't attempt to restore previous credit values.
    }
};
