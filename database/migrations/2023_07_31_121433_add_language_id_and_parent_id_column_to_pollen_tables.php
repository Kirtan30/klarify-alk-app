<?php

use App\Models\PollenCity;
use App\Models\PollenRegion;
use App\Models\PollenState;
use App\Models\User;
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
        Schema::disableForeignKeyConstraints();

        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->after('user_id');
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('pollen_cities')->onDelete('cascade');
        });

        Schema::table('pollen_regions', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->after('user_id');
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('pollen_regions')->onDelete('cascade');
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->after('user_id');
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('pollen_states')->onDelete('cascade');
        });

        $shops = User::with('languages')->get();
        foreach ($shops as $shop) {
            $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
            if ($defaultLanguage) {
                PollenCity::where('user_id', $shop->id)->update(['language_id' => $defaultLanguage->id]);
                PollenRegion::where('user_id', $shop->id)->update(['language_id' => $defaultLanguage->id]);
                PollenState::where('user_id', $shop->id)->update(['language_id' => $defaultLanguage->id]);
            }
        }

        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        Schema::table('pollen_regions', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pollen_cities', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['language_id', 'parent_id']);
        });

        Schema::table('pollen_regions', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['language_id', 'parent_id']);
        });

        Schema::table('pollen_states', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['language_id', 'parent_id']);
        });
    }
};
