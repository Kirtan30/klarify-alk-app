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
            $table->decimal('latitude', 11, 8)->after('handle')->nullable();
            $table->decimal('longitude', 11, 8)->after('latitude')->nullable();
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->decimal('latitude', 11, 8)->after('handle')->nullable();
            $table->decimal('longitude', 11, 8)->after('latitude')->nullable();
        });

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->decimal('latitude', 11, 8)->after('handle')->nullable();
            $table->decimal('longitude', 11, 8)->after('latitude')->nullable();
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
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
};
