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
        Schema::table('fitness_classes', function (Blueprint $table) {
            if (!Schema::hasColumn('fitness_classes', 'instructor_reminder_sent_at')) {
                $table->timestamp('instructor_reminder_sent_at')->nullable()->after('location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            if (Schema::hasColumn('fitness_classes', 'instructor_reminder_sent_at')) {
                $table->dropColumn('instructor_reminder_sent_at');
            }
        });
    }
};
