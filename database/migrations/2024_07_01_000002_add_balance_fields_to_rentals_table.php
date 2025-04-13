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
            $table->boolean('is_balance_released')->default(false)->after('status');
            $table->timestamp('balance_released_at')->nullable()->after('is_balance_released');
            $table->decimal('total_amount', 10, 2)->nullable()->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('is_balance_released');
            $table->dropColumn('balance_released_at');
            $table->dropColumn('total_amount');
        });
    }
}; 