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
            $table->foreignId('offer_id')->constrained()->onDelete('restrict');
            $table->foreignId('rental_status_id')->constrained('rental_status');
            $table->timestamps();
            $table->softDeletes();

            $table->index('offer_id');
            $table->index('rental_status_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentals');
    }
};