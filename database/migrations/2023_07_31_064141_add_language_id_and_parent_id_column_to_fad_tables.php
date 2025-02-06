<?php

use App\Models\FadCity;
use App\Models\FadPageContent;
use App\Models\FadRegion;
use App\Models\FadState;
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

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->after('user_id');
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('fad_cities')->onDelete('cascade');
        });

        Schema::table('fad_regions', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->after('user_id');
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('fad_regions')->onDelete('cascade');
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->unsignedBigInteger('language_id')->after('user_id');
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('fad_states')->onDelete('cascade');
        });

        $shops = User::with('languages')->get();
        foreach ($shops as $shop) {
            $defaultLanguage = $shop->languages->where('pivot.default', true)->first();
            if ($defaultLanguage) {
                FadCity::where('user_id', $shop->id)->update(['language_id' => $defaultLanguage->id]);
                FadRegion::where('user_id', $shop->id)->update(['language_id' => $defaultLanguage->id]);
                FadState::where('user_id', $shop->id)->update(['language_id' => $defaultLanguage->id]);
            }
        }

        Schema::table('fad_cities', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        Schema::table('fad_regions', function (Blueprint $table) {
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
        });

        Schema::table('fad_states', function (Blueprint $table) {
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
        Schema::table('fad_cities', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['language_id', 'parent_id']);
        });

        Schema::table('fad_regions', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['language_id', 'parent_id']);
        });

        Schema::table('fad_states', function (Blueprint $table) {
            $table->dropForeign(['language_id']);
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['language_id', 'parent_id']);
        });
    }
};
