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
        Schema::table('pollen_page_contents', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('content');
        });

        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('pollen_page_content_id');
        });

        Schema::table('pollen_regions', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('pollen_page_content_id');
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('pollen_page_content_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->dropColumn('variables');
        });

        Schema::table('pollen_regions', function (Blueprint $table) {
            $table->dropColumn('variables');
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->dropColumn('variables');
        });

        Schema::table('pollen_page_contents', function (Blueprint $table) {
            $table->dropColumn('variables');
        });
    }
};
