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
        Schema::create('allergy_test_question_options', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('allergy_test_id');
            $table->unsignedBigInteger('allergy_test_question_id');
            $table->string('order')->default(0);
            $table->string('name')->nullable();
            $table->integer('weightage')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('allergy_test_id')->references('id')->on('allergy_tests')->onDelete('cascade');
            $table->foreign('allergy_test_question_id')->references('id')->on('allergy_test_questions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allergy_test_question_options');
    }
};
