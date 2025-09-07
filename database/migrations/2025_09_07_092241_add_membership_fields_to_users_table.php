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
        Schema::table('users', function (Blueprint $table) {
            // Only add columns that don't exist yet
            if (!Schema::hasColumn('users', 'monthly_credits')) {
                $table->integer('monthly_credits')->default(0);
            }
            if (!Schema::hasColumn('users', 'credits_last_refreshed')) {
                $table->date('credits_last_refreshed')->nullable();
            }
            if (!Schema::hasColumn('users', 'membership_start_date')) {
                $table->date('membership_start_date')->nullable();
            }
            if (!Schema::hasColumn('users', 'membership_end_date')) {
                $table->date('membership_end_date')->nullable();
            }
        });
        
        // Add foreign key constraint if it doesn't exist
        if (Schema::hasColumn('users', 'membership_id') && Schema::hasTable('memberships')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key might already exist, ignore
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'membership_id')) {
                try {
                    $table->dropForeign(['membership_id']);
                } catch (\Exception $e) {
                    // Foreign key might not exist, ignore
                }
            }
            $table->dropColumn(['monthly_credits', 'credits_last_refreshed', 'membership_start_date', 'membership_end_date']);
        });
    }
};
