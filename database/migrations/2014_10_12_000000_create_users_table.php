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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['user', 'admin', 'super_admin'])->default('user');
            $table->string('email')->unique();
            $table->string('phone_number')->unique();
            $table->string('password');
            $table->boolean('terms_accepted')->default(false);
            $table->timestamp('terms_accepted_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->rememberToken();
            $table->boolean('onboarding_completed')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['email', 'phone_number']);
        });

        // Create stripe_accounts table for Stripe-specific user data
        Schema::create('stripe_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('customer_id')->nullable();
            $table->string('account_id')->nullable();
            $table->boolean('account_enabled')->default(false);
            $table->json('account_details')->nullable();
            $table->timestamp('account_verified_at')->nullable();
            $table->string('default_payment_method')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index('customer_id');
            $table->index('account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_accounts');
        Schema::dropIfExists('users');
    }
}; 