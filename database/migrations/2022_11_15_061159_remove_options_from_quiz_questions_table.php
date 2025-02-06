<?php

use App\Models\QuestionOption;
use App\Models\Quiz;
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
        Schema::table('quiz_questions', function (Blueprint $table) {

            $quizzes = Quiz::with(['user', 'questions'])->get();
            foreach ($quizzes as $quiz) {
                $userId = data_get($quiz, 'user.id');
                $questions = data_get($quiz, 'questions', []);

                foreach ($questions as $question) {
                    $options = data_get($question, 'options', []);

                    foreach ($options as $optionIndex => $option) {
                        QuestionOption::create([
                            'user_id' => $userId,
                            'quiz_id' => data_get($quiz, 'id'),
                            'question_id' => data_get($question, 'id'),
                            'type' => 'title',
                            'title' => data_get($option, 'title'),
                            'description' => data_get($option, 'description'),
                            'correct' => (
                                data_get($option, 'correct') === 'true' ||
                                data_get($option, 'correct') === true ||
                                data_get($option, 'correct') === 1) ? 1 : 0,
                            'order' => data_get($option, 'order') || data_get($option, 'order') === 0 ? data_get($option, 'order') : $optionIndex,
                            'created_at' => data_get($question, 'created_at'),
                            'updated_at' => data_get($question, 'updated_at')
                        ]);
                    }
                }
            }
            $table->dropColumn('options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->json('options')->after('label')->nullable();
        });
    }
};
