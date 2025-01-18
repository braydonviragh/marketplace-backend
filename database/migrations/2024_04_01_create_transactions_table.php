<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('rental_id')->nullable()->constrained();
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['add', 'remove']);
            $table->timestamps();
            $table->softDeletes();

            // Add indexes for frequently queried columns
            $table->index('user_id');
            $table->index('rental_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_transactions');
    }
}; 