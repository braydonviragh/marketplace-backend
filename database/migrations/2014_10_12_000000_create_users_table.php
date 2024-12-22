<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('password')->nullable(); // Nullable for social auth
            $table->string('phone_number')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('bio')->nullable();
            
            // OAuth Fields
            $table->string('provider')->nullable(); // 'apple', 'google', 'instagram'
            $table->string('provider_id')->nullable();
            $table->json('provider_token')->nullable(); // Store OAuth tokens securely
            
            // Account Status & Security
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->string('two_factor_recovery_codes')->nullable();
            
            // Account Type & Roles
            $table->enum('account_type', ['personal', 'business'])->default('personal');
            $table->enum('role', ['user', 'admin', 'moderator'])->default('user');
            
            // Region & Localization
            $table->string('timezone')->default('America/Toronto');
            $table->string('locale')->default('en');
            $table->string('country_code')->default('CA');
            $table->string('region_code')->default('ON'); // Province/State
            
            // Tracking & Security
            $table->string('last_login_ip')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->integer('login_attempts')->default(0);
            $table->timestamp('locked_until')->nullable();
            
            // Standard Fields
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for Performance
            $table->index(['email', 'provider', 'provider_id']);
            $table->index(['country_code', 'region_code']);
            $table->index('account_type');
            $table->index('role');
            $table->index('is_active');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}; 