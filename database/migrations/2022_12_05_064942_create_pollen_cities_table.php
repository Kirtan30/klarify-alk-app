<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pollen_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('pollen_region_id')->nullable()->constrained()->references('id')->on('pollen_regions')->cascadeOnDelete();
            $table->foreignId('pollen_state_id')->nullable()->constrained()->references('id')->on('pollen_states')->cascadeOnDelete();
            $table->string('name');
            $table->string('handle');
            $table->decimal('latitude', 11, 8);
            $table->decimal('longitude', 11, 8);
            $table->boolean('has_static_content')->default(false);
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pollen_cities');
    }
};
