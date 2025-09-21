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
            if (!Schema::hasColumn('users', 'credits_expires_at')) {
                $table->date('credits_expires_at')->nullable()->after('credits');
            }
            if (!Schema::hasColumn('users', 'unlimited_pass_expires_at')) {
                $table->date('unlimited_pass_expires_at')->nullable()->after('credits_expires_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'unlimited_pass_expires_at')) {
                $table->dropColumn('unlimited_pass_expires_at');
            }
            if (Schema::hasColumn('users', 'credits_expires_at')) {
                $table->dropColumn('credits_expires_at');
            }
        });
    }
};
