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
            $table->string('user_login')->nullable()->after('id');
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('nickname')->nullable()->after('last_name');
            $table->string('display_name')->nullable()->after('nickname');
            $table->timestamp('user_registered')->nullable()->after('email_verified_at');
            $table->string('phone_number')->nullable()->after('user_registered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'user_login',
                'first_name',
                'last_name',
                'nickname',
                'display_name',
                'user_registered',
                'phone_number'
            ]);
        });
    }
};
