<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we'll just update the records since we can't modify the column default directly
        \DB::table('users')
            ->where('monthly_credits', 0)
            ->orWhereNull('monthly_credits')
            ->update(['monthly_credits' => 5]);
            
        // Also set credits to monthly_credits for existing users
        \DB::table('users')
            ->where('credits', 0)
            ->orWhereNull('credits')
            ->update(['credits' => 5]);
        
        // Make sure credits_last_refreshed exists
        if (!Schema::hasColumn('users', 'credits_last_refreshed')) {
            Schema::table('users', function (Blueprint $table) {
                $table->date('credits_last_refreshed')->nullable()->after('monthly_credits');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert the default value change
    }
};
