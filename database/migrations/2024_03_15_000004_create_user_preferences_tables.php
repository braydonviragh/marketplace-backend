<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // User detailed sizes (combining letter, number, and waist sizes)
        Schema::create('user_detailed_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('letter_size_id')->nullable()->constrained('letter_sizes')->onDelete('set null');
            $table->foreignId('waist_size_id')->nullable()->constrained('waist_sizes')->onDelete('set null');
            $table->foreignId('number_size_id')->nullable()->constrained('number_sizes')->onDelete('set null');
            $table->timestamps();

            // Create a composite unique constraint
            $table->unique(['user_id', 'letter_size_id', 'waist_size_id', 'number_size_id'], 'user_sizes_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_detailed_sizes');
    }
}; 