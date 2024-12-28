<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('profile_picture')->nullable();
            $table->date('birthday')->nullable();
            $table->string('zip_code');
            $table->enum('style_preference', ['male', 'female', 'unisex'])->default('unisex');
            $table->string('language')->default('en');
            $table->json('preferences')->nullable();
            $table->timestamps();
            
            $table->index('zip_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}; 