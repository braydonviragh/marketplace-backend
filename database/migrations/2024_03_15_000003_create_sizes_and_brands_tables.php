<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Brands table
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->string('slug')->unique();
        });

        //General colors table 
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hex_code')->unique();
            $table->string('slug')->unique();
        });

        // General sizes table (XS, S, M, etc.)
        Schema::create('letter_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
        });

        // Number sizes table (00-22)
        Schema::create('number_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
        });

        // Waist sizes table (24-48)
        Schema::create('waist_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
        });

        // Add shoe sizes table (5-15)
        Schema::create('shoe_sizes', function (Blueprint $table) {
            $table->id();
            $table->decimal('size', 3, 1); // Allows for half sizes (e.g., 8.5)
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->integer('order'); // For proper sorting
            $table->unique('size');
        });
    }

    public function down()
    {
        Schema::dropIfExists('shoe_sizes');
        Schema::dropIfExists('waist_sizes');
        Schema::dropIfExists('number_sizes');
        Schema::dropIfExists('letter_sizes');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('colors');
    }
};