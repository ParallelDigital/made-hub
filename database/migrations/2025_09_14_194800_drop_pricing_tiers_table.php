<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop pricing_tiers table if it exists
        if (Schema::hasTable('pricing_tiers')) {
            Schema::drop('pricing_tiers');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left blank. We don't restore removed pricing data.
    }
};
