<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure credits_last_refreshed exists
        if (!Schema::hasColumn('users', 'credits_last_refreshed')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('credits_last_refreshed')->nullable()->after('monthly_credits');
            });
        }

        // Only set monthly_credits for ACTIVE members; leave non-members and legacy credits untouched
        $today = now()->toDateString();
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
                  ->orWhere('monthly_credits', 0);
            })
            ->update(['monthly_credits' => 5]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert the default value change
    }
};
