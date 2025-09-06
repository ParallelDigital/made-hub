<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks for SQLite
        DB::statement('PRAGMA foreign_keys=OFF');
        
        // Create new bookings table with correct foreign key
        Schema::create('bookings_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('fitness_class_id')->constrained('fitness_classes')->onDelete('cascade');
            $table->string('status')->default('confirmed');
            $table->string('booking_type')->nullable();
            $table->decimal('amount_paid', 8, 2)->nullable();
            $table->timestamp('booked_at');
            $table->string('stripe_session_id')->nullable()->unique();
            $table->timestamps();
        });
        
        // Copy data from old table to new table
        DB::statement('INSERT INTO bookings_new (id, user_id, fitness_class_id, status, booking_type, amount_paid, booked_at, stripe_session_id, created_at, updated_at) 
                       SELECT id, user_id, fitness_class_id, status, booking_type, amount_paid, booked_at, stripe_session_id, created_at, updated_at 
                       FROM bookings');
        
        // Drop old table and rename new table
        Schema::drop('bookings');
        Schema::rename('bookings_new', 'bookings');
        
        // Re-enable foreign key checks
        DB::statement('PRAGMA foreign_keys=ON');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not easily reversible due to foreign key changes
        // Would need to recreate the old structure
    }
};
