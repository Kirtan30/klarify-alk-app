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
        Schema::create('fad_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('fad_region_id')->nullable()->constrained()->references('id')->on('fad_regions')->cascadeOnDelete();
            $table->foreignId('fad_state_id')->nullable()->constrained()->references('id')->on('fad_states')->cascadeOnDelete();
            $table->string('name');
            $table->string('handle');
            $table->boolean('has_static_content')->default(false);
            $table->boolean('enabled')->default(false);
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
        Schema::dropIfExists('fad_cities');
    }
};
