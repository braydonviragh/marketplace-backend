<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('app_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('restrict');
            $table->decimal('amount', 10, 2);
            $table->timestamps();
            
            $table->index('rental_id');
        });

        Schema::create('app_balance', function (Blueprint $table) {
            $table->id();
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamp('last_updated_at');
        });

        // Insert initial balance record
        DB::table('app_balance')->insert([
            'balance' => 0,
            'last_updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('app_transactions');
        Schema::dropIfExists('app_balance');
    }
}; 