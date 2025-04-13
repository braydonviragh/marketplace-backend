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
        if (!Schema::hasTable('stripe_transactions')) {
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_transactions');
    }
}; 