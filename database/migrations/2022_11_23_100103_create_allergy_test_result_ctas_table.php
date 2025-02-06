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
        Schema::create('allergy_test_result_ctas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('allergy_test_id');
            $table->unsignedBigInteger('allergy_test_result_id');
            $table->string('text')->nullable();
            $table->string('link')->nullable();
            $table->boolean('target_blank')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('allergy_test_id')->references('id')->on('allergy_tests')->onDelete('cascade');
            $table->foreign('allergy_test_result_id')->references('id')->on('allergy_test_results')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allergy_test_result_ctas');
    }
};
