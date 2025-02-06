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
        Schema::table('user_language', function (Blueprint $table) {
            $table->string('fad_iframe_page')->nullable()->after('lexicon_page');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_language', function (Blueprint $table) {
            $table->dropColumn('fad_iframe_page');
        });
    }
};
