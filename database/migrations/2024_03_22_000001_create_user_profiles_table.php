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
            $table->string('username')->unique();
            $table->string('name');
            $table->string('profile_picture')->nullable();
            $table->date('birthday')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->foreignId('style_id')->constrained()->onDelete('cascade');
            $table->string('language')->default('en');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}; 