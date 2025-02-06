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
        Schema::table('pollen_regions', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->foreignId('pollen_page_content_id')->nullable()->after('has_static_content')->constrained()->references('id')->on('pollen_page_contents');
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->foreignId('pollen_page_content_id')->nullable()->after('has_static_content')->constrained()->references('id')->on('pollen_page_contents');
        });

        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->foreignId('pollen_page_content_id')->nullable()->after('has_static_content')->constrained()->references('id')->on('pollen_page_contents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pollen_regions', function (Blueprint $table) {
            $table->longText('content')->nullable();
            $table->dropForeign(['pollen_page_content_id']);
            $table->dropColumn('pollen_page_content_id');
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->longText('content')->nullable();
            $table->dropForeign(['pollen_page_content_id']);
            $table->dropColumn('pollen_page_content_id');
        });

        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->longText('content')->nullable();
            $table->dropForeign(['pollen_page_content_id']);
            $table->dropColumn('pollen_page_content_id');
        });
    }
};
