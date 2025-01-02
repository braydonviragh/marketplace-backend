<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('styles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
        });

        // Seed initial styles
        DB::table('styles')->insert([
            ['name' => 'Mens', 'slug' => 'mens'],
            ['name' => 'Womens', 'slug' => 'womens'],
            ['name' => 'Unisex', 'slug' => 'unisex'],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('styles');
    }
}; 