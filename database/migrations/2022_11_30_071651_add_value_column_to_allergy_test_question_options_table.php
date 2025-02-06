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
        Schema::table('allergy_test_question_options', function (Blueprint $table) {
            $table->integer('value')->default(0)->after('weightage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('allergy_test_question_options', function (Blueprint $table) {
            $table->dropColumn('value');
        });
    }
};
