<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create payment_status table
        Schema::create('payment_status', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();

            $table->index('slug');
        });

        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('rental_id')->nullable()->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['add', 'remove']);
            $table->foreignId('payment_status_id')->default(1)->constrained('payment_status');
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for frequently queried columns
            $table->index('user_id');
            $table->index('rental_id');
            $table->index('payment_status_id');
        });

        // Create stripe_transactions table for Stripe-specific data
        Schema::create('stripe_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_transaction_id')->constrained('user_transactions')->onDelete('cascade');
            $table->string('transfer_id')->nullable();
            $table->string('payout_id')->nullable();
            $table->string('payment_intent_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('transfer_id');
            $table->index('payout_id');
            $table->index('payment_intent_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('stripe_transactions');
        Schema::dropIfExists('user_transactions');
        Schema::dropIfExists('payment_status');
    }
}; 