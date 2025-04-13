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
        // Check if the table already exists and skip if it does
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('rental_id')->constrained('rentals')->onDelete('cascade');
                $table->foreignId('payer_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('payee_id')->constrained('users')->onDelete('cascade');
                $table->decimal('amount', 10, 2);
                $table->decimal('platform_fee', 10, 2)->comment('20% of the amount');
                $table->decimal('owner_amount', 10, 2)->comment('80% of the amount');
                $table->string('currency', 3)->default('usd');
                $table->string('payment_method');
                $table->string('status');
                $table->string('stripe_payment_intent_id')->nullable();
                $table->string('refund_id')->nullable();
                $table->decimal('refunded_amount', 10, 2)->nullable();
                $table->json('payment_details')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
}; 