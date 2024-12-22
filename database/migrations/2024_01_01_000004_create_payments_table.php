<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('restrict');
            $table->foreignId('payer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('payee_id')->constrained('users')->onDelete('restrict');
            
            // Payment Details
            $table->string('payment_method'); // apple_pay, paypal
            $table->string('payment_id')->unique(); // External payment ID
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('CAD');
            
            // Status
            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
                'refunded',
                'partially_refunded'
            ])->default('pending');
            
            // Payment Processing
            $table->json('payment_details')->nullable();
            $table->json('refund_details')->nullable();
            $table->text('failure_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['payment_id', 'status']);
            $table->index(['payer_id', 'status']);
            $table->index(['payee_id', 'status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}; 