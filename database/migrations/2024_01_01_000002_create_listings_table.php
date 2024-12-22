<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            
            // Basic Information
            $table->string('title');
            $table->text('description');
            $table->string('brand')->nullable();
            $table->string('size');
            $table->enum('condition', ['new', 'like_new', 'good', 'fair'])->default('good');
            
            // Pricing
            $table->decimal('daily_price', 10, 2);
            $table->decimal('weekly_price', 10, 2)->nullable();
            $table->decimal('monthly_price', 10, 2)->nullable();
            $table->decimal('security_deposit', 10, 2);
            
            // Location
            $table->string('city');
            $table->string('province');
            $table->string('postal_code');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();
            $table->point('location')->notNull();
            $table->spatialIndex('location');
            
            // Availability
            $table->boolean('is_available')->default(true);
            $table->json('availability_calendar')->nullable();
            $table->json('unavailable_dates')->nullable();
            
            // Metadata
            $table->json('specifications')->nullable(); // Color, material, etc.
            $table->json('care_instructions')->nullable();
            $table->integer('views_count')->default(0);
            $table->boolean('featured')->default(false);
            
            // Moderation
            $table->boolean('is_approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['user_id', 'is_available']);
            $table->index(['category_id', 'is_available']);
            $table->index(['city', 'province', 'is_available']);
            $table->index('daily_price');
            $table->index('latitude');
            $table->index('longitude');
        });
    }

    public function down()
    {
        Schema::dropIfExists('listings');
    }
}; 