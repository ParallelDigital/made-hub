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
        // First, add the column as nullable to avoid issues with existing data
        Schema::table('fitness_classes', function (Blueprint $table) {
            $table->foreignId('class_type_id')
                  ->nullable()
                  ->after('id')
                  ->constrained('class_types')
                  ->nullOnDelete();
        });

        // If you want to set a default class_type_id for existing records,
        // you can do it here with a raw SQL update
        // DB::table('fitness_classes')->update(['class_type_id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            $table->dropForeign(['class_type_id']);
            $table->dropColumn('class_type_id');
        });
    }
};
