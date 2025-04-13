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
        Schema::table('rentals', function (Blueprint $table) {
            // Check if columns don't exist before adding them
            if (!Schema::hasColumn('rentals', 'is_balance_released')) {
                $table->boolean('is_balance_released')->default(false);
            }
            
            if (!Schema::hasColumn('rentals', 'balance_released_at')) {
                $table->timestamp('balance_released_at')->nullable();
            }
            
            if (!Schema::hasColumn('rentals', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn(['is_balance_released', 'balance_released_at', 'total_amount']);
        });
    }
}; 