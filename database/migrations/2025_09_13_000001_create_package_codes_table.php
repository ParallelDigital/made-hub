<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('package_type');
            $table->unsignedInteger('classes')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('redeemed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_codes');
    }
};
