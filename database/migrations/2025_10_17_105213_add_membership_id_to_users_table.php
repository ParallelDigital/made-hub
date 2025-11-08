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
            // Check if column doesn't already exist
            if (!Schema::hasColumn('users', 'membership_id')) {
                $table->unsignedBigInteger('membership_id')->nullable()->after('pin_code');
            }
        });
        
        // Add foreign key separately (SQLite handles this better)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->foreign('membership_id')->references('id')->on('memberships')->onDelete('set null');
            });
        } catch (\Exception $e) {
            // Foreign key might already exist, that's ok
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['membership_id']);
            $table->dropColumn('membership_id');
        });
    }
};
