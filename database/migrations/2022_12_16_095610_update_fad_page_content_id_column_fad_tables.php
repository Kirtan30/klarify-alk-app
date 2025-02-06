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
            $table->dropForeign(['fad_page_content_id']);
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->change()->constrained()->references('id')->on('fad_page_contents')->nullOnDelete();
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->dropForeign(['fad_page_content_id']);
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->change()->constrained()->references('id')->on('fad_page_contents')->nullOnDelete();
        });

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->dropForeign(['fad_page_content_id']);
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->change()->constrained()->references('id')->on('fad_page_contents')->nullOnDelete();
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
            $table->dropForeign(['fad_page_content_id']);
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->change()->constrained()->references('id')->on('fad_page_contents');
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->dropForeign(['fad_page_content_id']);
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->change()->constrained()->references('id')->on('fad_page_contents');
        });

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->dropForeign(['fad_page_content_id']);
            $table->foreignId('fad_page_content_id')->nullable()->after('has_static_content')->change()->constrained()->references('id')->on('fad_page_contents');
        });
    }
};
