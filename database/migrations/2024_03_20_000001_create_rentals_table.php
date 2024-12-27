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
            $table->foreignId('offer_id')->nullable()->constrained('offers')->onDelete('set null');
            $table->dateTime('rental_from');
            $table->dateTime('rental_to');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id']);
            $table->index(['product_id']);
            $table->index('offer_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentals');
    }
};