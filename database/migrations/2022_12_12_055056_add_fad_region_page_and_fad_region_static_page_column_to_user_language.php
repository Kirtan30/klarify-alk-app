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
            $table->string('fad_region_page')->nullable()->after('fad_static_page');
            $table->string('fad_region_static_page')->nullable()->after('fad_region_page');
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
            $table->dropColumn(['fad_region_page', 'fad_region_static_page']);
        });
    }
};
