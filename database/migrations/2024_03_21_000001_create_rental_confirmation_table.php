<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rental_confirmation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['owner', 'renter']);
            $table->timestamps();

            $table->unique(['rental_id', 'user_id']);
            $table->index(['rental_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rental_confirmation');
    }
}; 