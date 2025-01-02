<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('code');
            $table->enum('type', ['registration', 'password_reset']);
            $table->boolean('used')->default(false);
            $table->timestamp('expires_at');
            $table->timestamps();
            
            $table->index(['phone_number', 'code']);
            $table->index(['email', 'code']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('verification_codes');
    }
}; 