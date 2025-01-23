<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('username')->unique();
            $table->string('name');
            $table->string('profile_picture')->nullable();
            $table->date('birthday')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('city')->nullable();
            $table->foreignId('province_id')->constrained()->onDelete('cascade');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->point('location')->nullable();
            $table->foreignId('style_id')->constrained()->onDelete('cascade');
            $table->string('language')->default('en');
        });

        // Set default value for location column and create spatial index
        DB::statement('ALTER TABLE user_profiles MODIFY location POINT NOT NULL');
        DB::statement('UPDATE user_profiles SET location = ST_GeomFromText("POINT(0 0)")');
        DB::statement('ALTER TABLE user_profiles ADD SPATIAL INDEX user_profiles_location_spatialindex(location)');
    }

    public function down()
    {
        Schema::dropIfExists('user_profiles');
    }
}; 