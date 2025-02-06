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
        Schema::create('clinic_week_day_opening_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinic_week_day_id')->constrained()->references('id')->on('clinic_week_day')->cascadeOnDelete();
            $table->integer('opening_second');
            $table->string('opening_time');
            $table->integer('closing_second');
            $table->string('closing_time');
            $table->boolean('optional')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clinic_week_day_opening_hours');
    }
};
