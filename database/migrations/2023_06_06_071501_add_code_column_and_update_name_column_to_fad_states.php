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
        Schema::table('fad_states', function (Blueprint $table) {
            $table->string('code')->after('name')->nullable();
            $table->string('name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fad_states', function (Blueprint $table) {
            $table->dropColumn('code');
            $table->string('name')->nullable(false)->change();
        });
    }
};
