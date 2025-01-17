<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('restrict');
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->foreignId('rental_status_id')->constrained('rental_status');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id']);
            $table->index(['product_id']);
            $table->index('rental_status_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentals');
    }
};