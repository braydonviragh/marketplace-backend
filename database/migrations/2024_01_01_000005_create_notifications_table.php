<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Notification Details
            $table->string('type'); // rental_request, payment_received, etc.
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            
            // Delivery Status
            $table->enum('status', ['pending', 'sent', 'failed', 'read'])->default('pending');
            $table->string('channel')->default('push'); // push, email, sms
            $table->dateTime('read_at')->nullable();
            $table->dateTime('sent_at')->nullable();
            
            // Error Tracking
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['type', 'status']);
            $table->index('sent_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}; 