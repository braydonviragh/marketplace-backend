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
        // Only add additional fields if the user_balances table already exists
        if (Schema::hasTable('user_balances')) {
            Schema::table('user_balances', function (Blueprint $table) {
                // Check if columns don't exist before adding them
                if (!Schema::hasColumn('user_balances', 'currency')) {
                    $table->string('currency', 3)->default('usd');
                }
                
                if (!Schema::hasColumn('user_balances', 'last_activity_at')) {
                    $table->timestamp('last_activity_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_balances')) {
            Schema::table('user_balances', function (Blueprint $table) {
                $table->dropColumn(['currency', 'last_activity_at']);
            });
        }
    }
}; 