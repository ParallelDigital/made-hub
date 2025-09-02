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
        Schema::create('pricing_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['class', 'membership', 'package']);
            $table->decimal('base_price', 8, 2);
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->decimal('final_price', 8, 2);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();
            $table->json('conditions')->nullable(); // Additional conditions
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_tiers');
    }
};
