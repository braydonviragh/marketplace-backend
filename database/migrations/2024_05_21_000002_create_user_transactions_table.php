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
        if (!Schema::hasTable('user_transactions')) {
            Schema::create('user_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained();
                $table->foreignId('rental_id')->nullable()->constrained();
                $table->decimal('amount', 10, 2);
                $table->enum('type', ['add', 'remove']);
                
                // Make sure the payment_status table has been created before referencing it
                if (Schema::hasTable('payment_status')) {
                    $table->foreignId('payment_status_id')->default(1)->constrained('payment_status');
                } else {
                    $table->foreignId('payment_status_id')->default(1)->nullable();
                }
                
                $table->timestamps();
                $table->softDeletes();

                // Add indexes for frequently queried columns
                $table->index('user_id');
                $table->index('rental_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
    }
}; 