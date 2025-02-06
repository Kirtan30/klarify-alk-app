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
        Schema::table('fad_regions', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->constrained()->references('id')->on('fad_page_contents');
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->constrained()->references('id')->on('fad_page_contents');
        });

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->dropColumn('content');
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->constrained()->references('id')->on('fad_page_contents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fad_regions', function (Blueprint $table) {
            $table->longText('content')->nullable();
            $table->dropForeign(['fad_page_content_id']);
            $table->dropColumn('fad_page_content_id');
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->longText('content')->nullable();
            $table->dropForeign(['fad_page_content_id']);
            $table->dropColumn('fad_page_content_id');
        });

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->longText('content')->nullable();
            $table->dropForeign(['fad_page_content_id']);
            $table->dropColumn('fad_page_content_id');
        });
    }
};
