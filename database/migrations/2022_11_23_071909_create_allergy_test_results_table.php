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
        Schema::create('allergy_test_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('allergy_test_id');
            $table->string('type')->default('resultSimple');
            $table->json('percentage_range')->nullable();
            $table->string('label')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('instruction')->nullable();
            $table->json('result_data')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('allergy_test_id')->references('id')->on('allergy_tests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allergy_test_results');
    }
};
