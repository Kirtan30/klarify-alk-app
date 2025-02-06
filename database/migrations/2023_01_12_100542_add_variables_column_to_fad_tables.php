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
        Schema::table('fad_page_contents', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('content');
        });

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('fad_page_content_id');
        });

        Schema::table('fad_regions', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('fad_page_content_id');
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->json('variables')->nullable()->after('fad_page_content_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fad_cities', function (Blueprint $table) {
            $table->dropColumn('variables');
        });

        Schema::table('fad_regions', function (Blueprint $table) {
            $table->dropColumn('variables');
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->dropColumn('variables');
        });

        Schema::table('fad_page_contents', function (Blueprint $table) {
            $table->dropColumn('variables');
        });
    }
};
