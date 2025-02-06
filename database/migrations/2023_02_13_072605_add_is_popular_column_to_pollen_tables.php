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
            $table->boolean('is_popular')->after('has_static_content')->default(0);
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->boolean('is_popular')->after('has_static_content')->default(0);
        });

        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->boolean('is_popular')->after('has_static_content')->default(0);
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
            $table->dropColumn('is_popular');
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->dropColumn('is_popular');
        });

        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->dropColumn('is_popular');
        });
    }
};
