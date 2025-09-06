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
            $table->enum('recurring_frequency', ['none', 'weekly', 'biweekly', 'monthly'])->default('none')->after('recurring_weekly');
            $table->date('recurring_until')->nullable()->after('recurring_frequency');
            $table->unsignedBigInteger('parent_class_id')->nullable()->after('recurring_until');
            $table->foreign('parent_class_id')->references('id')->on('fitness_classes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            $table->dropForeign(['parent_class_id']);
            $table->dropColumn(['recurring_frequency', 'recurring_until', 'parent_class_id']);
        });
    }
};
