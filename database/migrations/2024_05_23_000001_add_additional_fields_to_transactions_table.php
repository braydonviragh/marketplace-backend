<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // If the transactions table already exists (created by 2024_05_22_000003_create_transactions_table.php)
        // Just add any missing columns
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Check if columns don't exist before adding them
                if (!Schema::hasColumn('transactions', 'reference_id')) {
                    $table->string('reference_id')->nullable();
                }
                
                if (!Schema::hasColumn('transactions', 'payment_method')) {
                    $table->string('payment_method')->nullable();
                }
                
                if (!Schema::hasColumn('transactions', 'currency')) {
                    $table->string('currency', 3)->default('usd');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn(['reference_id', 'payment_method', 'currency']);
            });
        }
    }
}; 