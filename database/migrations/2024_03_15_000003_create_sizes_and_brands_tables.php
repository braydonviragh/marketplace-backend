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
        });

        //General colors table 
        Schema::create('colors', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('hex_code')->unique();
            $table->string('slug')->unique();
        });

        // General sizes table (XS, S, M, etc.)
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->string('size_name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
        });

        // Number sizes table (00-22)
        Schema::create('number_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('display_name');
            $table->text('description')->nullable();
        });

        // Waist sizes table (24-48)
        Schema::create('waist_sizes', function (Blueprint $table) {
            $table->id();
            $table->integer('size');
            $table->string('display_name');
            $table->text('description')->nullable();
        });

        // Shoe sizes table (5-15 with half sizes)
        Schema::create('shoe_sizes', function (Blueprint $table) {
            $table->id();
            $table->decimal('size', 3, 1);
            $table->string('display_name');
            $table->text('description')->nullable();
        });

        // User-Brand preferences
        Schema::create('user_brand_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('brand_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'brand_id']);
        });

        // User-Size preferences for general sizes
        Schema::create('user_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('size_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['user_id', 'size_id']);
        });

        // User detailed size preferences - Fixed version
        Schema::create('user_detailed_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('size_type', ['number', 'waist', 'shoe']);
            $table->unsignedBigInteger('size_id');
            $table->timestamps();

            // Create a composite unique constraint
            $table->unique(['user_id', 'size_type', 'size_id']);
            
            // Add a check constraint instead of multiple foreign keys
            // Note: The actual foreign key validation will need to be handled at the application level
            $table->index(['size_type', 'size_id']);
        });

        // Custom sizes for specific measurements
        Schema::create('custom_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('measurement_type');
            $table->decimal('measurement_value', 5, 2);
            $table->string('unit')->default('inches');
            $table->timestamps();

            $table->unique(['user_id', 'measurement_type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('custom_sizes');
        Schema::dropIfExists('user_detailed_sizes');
        Schema::dropIfExists('user_sizes');
        Schema::dropIfExists('user_brand_preferences');
        Schema::dropIfExists('shoe_sizes');
        Schema::dropIfExists('waist_sizes');
        Schema::dropIfExists('number_sizes');
        Schema::dropIfExists('sizes');
        Schema::dropIfExists('brands');
    }
};