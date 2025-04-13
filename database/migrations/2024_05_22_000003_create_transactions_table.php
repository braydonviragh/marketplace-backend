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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type')->comment('credit or debit'); // credit for incoming, debit for outgoing
            $table->string('status')->default('pending');
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0.00);
            $table->string('description')->nullable();
            $table->string('transaction_type')->comment('rental, withdrawal, refund, etc.');
            $table->foreignId('source_id')->nullable()->comment('related offer/payment/etc. ID');
            $table->string('source_type')->nullable()->comment('related model type');
            $table->string('stripe_transaction_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
}; 