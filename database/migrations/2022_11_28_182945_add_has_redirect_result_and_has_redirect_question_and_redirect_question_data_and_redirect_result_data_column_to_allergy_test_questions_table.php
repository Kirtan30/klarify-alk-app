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
        Schema::table('allergy_test_questions', function (Blueprint $table) {
            $table->boolean('has_redirect_result')->default(false)->after('type');
            $table->boolean('has_redirect_question')->default(false)->after('has_redirect_result');
            $table->json('redirect_result_data')->after('question_data')->nullable();
            $table->json('redirect_question_data')->after('redirect_result_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allergy_test_questions', function (Blueprint $table) {
            $table->dropColumn(['has_redirect_result', 'has_redirect_question', 'redirect_result_data', 'redirect_question_data']);
        });
    }
};
