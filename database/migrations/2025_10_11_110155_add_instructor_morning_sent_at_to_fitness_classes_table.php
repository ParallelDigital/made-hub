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
            if (!Schema::hasColumn('fitness_classes', 'instructor_morning_sent_at')) {
                $table->timestamp('instructor_morning_sent_at')->nullable()->after('instructor_reminder_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            if (Schema::hasColumn('fitness_classes', 'instructor_morning_sent_at')) {
                $table->dropColumn('instructor_morning_sent_at');
            }
        });
    }
};
