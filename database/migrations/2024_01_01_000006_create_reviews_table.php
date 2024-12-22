<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rental_id')->constrained()->onDelete('restrict');
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('restrict');
            
            // Review Details
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->json('criteria_ratings')->nullable(); // For specific rating criteria
            
            // Moderation
            $table->boolean('is_approved')->default(true);
            $table->text('moderation_notes')->nullable();
            $table->timestamp('moderated_at')->nullable();
            
            // Response
            $table->text('owner_response')->nullable();
            $table->timestamp('response_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['reviewee_id', 'rating']);
            $table->index('is_approved');
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}; 