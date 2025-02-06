<?php

use App\Models\PollAnswer;
use App\Models\PollResponse;
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
        Schema::create('poll_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('poll_id')->constrained('polls')->onDelete('cascade');
            $table->foreignId('poll_answer_id')->constrained('poll_answers')->onDelete('cascade');
            $table->timestamps();
        });


        $pollAnswers = PollAnswer::all();
        foreach ($pollAnswers as $pollAnswer) {
            $count = $pollAnswer->count;
            $preparePollResponses = [];
            while ($count > 0) {
                $preparePollResponses[] = [
                    'user_id' => $pollAnswer->user_id,
                    'poll_id' => $pollAnswer->poll_id,
                    'poll_answer_id' => $pollAnswer->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $count--;
            }

            foreach (collect($preparePollResponses)->chunk(250) as $pollResponsesChunk) {
                PollResponse::insert($pollResponsesChunk->toArray());
            }

            sleep(1);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('poll_responses');
    }
};
