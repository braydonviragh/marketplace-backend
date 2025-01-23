<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('brand_id')->constrained()->onDelete('restrict');
            $table->foreignId('style_id')->constrained()->onDelete('restrict');
            
            // Basic Information
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 10, 2);
            
            // Size Information
            $table->enum('size_type', ['letter', 'number', 'waist', 'shoe', 'none'])->default('none');
            $table->foreignId('letter_size_id')->nullable()->constrained('letter_sizes')->onDelete('set null');
            $table->foreignId('number_size_id')->nullable()->constrained('number_sizes')->onDelete('set null');
            $table->foreignId('waist_size_id')->nullable()->constrained('waist_sizes')->onDelete('set null');
            $table->foreignId('shoe_size_id')->nullable()->constrained('shoe_sizes')->onDelete('set null');
            
            // Status and Visibility
            $table->boolean('is_available')->default(true);
            
            //Color 
            $table->foreignId('color_id')->nullable()->constrained()->onDelete('set null');
            
            // Metadata
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('user_id');
            $table->index('category_id');
            $table->index('style_id');
            $table->index('brand_id');
            $table->index('size_type');
            $table->index('letter_size_id');
            $table->index('number_size_id');
            $table->index('waist_size_id');
            $table->index('shoe_size_id');
            $table->index('is_available');
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}; 