<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained();
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_balances');
    }
}; 