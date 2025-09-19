<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            if (!Schema::hasColumn('fitness_classes', 'members_only')) {
                $table->boolean('members_only')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('fitness_classes', function (Blueprint $table) {
            if (Schema::hasColumn('fitness_classes', 'members_only')) {
                $table->dropColumn('members_only');
            }
        });
    }
};
