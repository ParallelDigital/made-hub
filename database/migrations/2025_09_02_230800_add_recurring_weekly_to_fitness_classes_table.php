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
            $table->boolean('recurring_weekly')->default(false)->after('active');
            $table->string('recurring_days')->nullable()->after('recurring_weekly'); // Store days like 'monday,wednesday,friday'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            $table->dropColumn(['recurring_weekly', 'recurring_days']);
        });
    }
};
