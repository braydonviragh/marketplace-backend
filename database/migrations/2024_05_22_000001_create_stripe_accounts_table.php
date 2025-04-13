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
        if (!Schema::hasTable('stripe_accounts')) {
            Schema::create('stripe_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('account_id')->nullable();
                $table->boolean('account_enabled')->default(false);
                $table->timestamp('account_verified_at')->nullable();
                $table->json('account_details')->nullable();
                $table->string('business_type')->nullable();
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
        Schema::dropIfExists('stripe_accounts');
    }
}; 