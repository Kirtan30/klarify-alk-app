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
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->string('type')->after('quiz_id')->default('title');
            $table->string('smiley')->after('title')->nullable();
            $table->string('image')->after('smiley')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('quiz_results', function (Blueprint $table) {
            $table->dropColumn(['type', 'smiley', 'image']);
        });
    }
};
