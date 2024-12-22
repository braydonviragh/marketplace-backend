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
            $table->foreignId('renter_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('owner_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('listing_id')->constrained()->onDelete('restrict');
            
            // Rental Details
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->decimal('total_price', 10, 2);
            $table->decimal('owner_earnings', 10, 2);
            $table->decimal('platform_fee', 10, 2);
            
            // Status Management
            $table->enum('status', [
                'pending',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled',
                'disputed'
            ])->default('pending');
            
            // Tracking
            $table->dateTime('picked_up_at')->nullable();
            $table->dateTime('returned_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->json('status_history')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['renter_id', 'status']);
            $table->index(['owner_id', 'status']);
            $table->index(['status', 'start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('rentals');
    }
}; 