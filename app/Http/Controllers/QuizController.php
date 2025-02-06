<?php

namespace App\Http\Controllers;

use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\QuizResult;
use App\Traits\QuizTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    use QuizTrait;

    public function index(Request $request) {
        $shop = $request->user();
        $quizzes = Quiz::where('user_id', $shop->id)->paginate(10);
        return response(['quizzes' => $quizzes], 200);
    }

    public function store(Request $request) {

        $request->validate([
            'title' => 'required',

            'questions' => 'nullable|array',
            'questions.*.title' => 'required',

            'questions.*.options' => 'array|min:1',
            'questions.*.options.*.title' => 'required_if:questions.*.options.*.type,title',
            'questions.*.options.*.image' => 'required_if:questions.*.options.*.type,image',

            'results' => 'nullable|array',
            'results.*.smiley' => 'required_if:results.*.type,smiley',
            'results.*.image' => 'required_if:results.*.type,image',
            'results.*.description' => 'required_if:results.*.type,description',
            'results.*.title' => 'required_if:results.*.type,title',
        ]);

        $shop = $request->user();

        try {
            $quizImageLink = $this->manageImage($request, $shop);

            $quiz = DB::transaction(function () use ($request, $shop, $quizImageLink) {
                $quiz = Quiz::create([
                    'uuid' => str()->uuid(),
                    'user_id' => $shop->id,
                    'theme' => $request->input('theme') ?: null,
                    'title' => $request->input('title'),
                    'label' => $request->input('label') ?: null,
                    'cta_label' => $request->input('cta_label') ?: null,
                    'image' => $quizImageLink
                ]);

                if (!empty($request->questions)) {

                    foreach ($request->input('questions') ?: [] as $question) {
                        $createdQuestion = QuizQuestion::create([
                            'user_id' => $shop->id,
                            'quiz_id' => $quiz->id,
                            'title' => data_get($question, 'title'),
                            'label' => data_get($question, 'label'),
                            'order' => data_get($question, 'order') ?: 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);

                        $options = data_get($question, 'options', []);

                        if (!empty($options)) {
                            $insertOptions = [];
                            foreach ($options as $option) {
                                $insertOptions[] = [
                                    'user_id' => $shop->id,
                                    'quiz_id' => $quiz->id,
                                    'question_id' => $createdQuestion->id,
                                    'type' => data_get($option, 'type') ?: 'title',
                                    'title' => data_get($option, 'type') != 'image' ? data_get($option, 'title') : null,
                                    'image' => null,
                                    'description' => data_get($option, 'description'),
                                    'correct' => data_get($option, 'correct') ?: 0,
                                    'order' => data_get($option, 'order'),
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ];
                            }

                            QuestionOption::insert($insertOptions);
                        }
                    }
                }

                if (!empty($request->results)) {

                    $results = [];
                    foreach ($request->input('results') ?: [] as $result) {
                        $results[] = [
                            'user_id' => $shop->id,
                            'quiz_id' => $quiz->id,
                            'type' => data_get($result, 'type') ?: 'title',
                            'percentage' => data_get($result, 'percentage') ?: 0,
                            'title' => data_get($result, 'title'),
                            'smiley' => data_get($result, 'type') == 'smiley' ? data_get($result, 'smiley') : null,
                            'image' => null,
                            'description' => data_get($result, 'type') == 'description' || data_get($result, 'type') == 'title' ? data_get($result, 'description') : null,
                            'cta_label' => data_get($result, 'cta_label'),
                            'cta_link' => data_get($result, 'cta_link'),
                            'text_label' => data_get($result, 'text_label'),
                            'text_link' => data_get($result, 'text_link'),
                            'order' => data_get($result, 'order') ?: 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }

                    QuizResult::insert($results);
                }

                return Quiz::with(['questions.options', 'results'])->findOrFail($quiz->id);
            });

            return response(['quiz' => $quiz, 'message' => 'Quiz created successfully'], 200);
        } catch (\Exception $e) {

            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, Quiz $quiz) {
        $shop = $request->user();
        $request->validate([

            'id' => 'required',
            'title' => 'required',

            'questions' => 'nullable|array',
            'questions.*.title' => 'required',

            'questions.*.options' => 'array|min:1',
            'questions.*.options.*.title' => 'required_if:questions.*.options.*.type,title',
//            'questions.*.options.*.image' => 'required_if:questions.*.options.*.type,image',

            'results' => 'nullable|array',
            'results.*.smiley' => 'required_if:results.*.type,smiley',
            'results.*.image' => 'required_if:results.*.type,image',
            'results.*.description' => 'required_if:results.*.type,description',
            'results.*.title' => 'required_if:results.*.type,title',
        ]);

        if ($request->input('image') !== $quiz->image) {
            $basePath = "quiz/images/$shop->name";
            $path = parse_url($quiz->image, PHP_URL_PATH);
            $path = "$basePath/" . basename($path);

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $quiz = DB::transaction(function () use ($request, $shop, $quiz) {
            $quiz->update([
                'theme' => $request->input('theme') ?: null,
                'user_id' => $shop->id,
                'title' => $request->input('title'),
                'label' => $request->input('label') ?: null,
                'cta_label' => $request->input('cta_label') ?: null,
                'image' => $request->input('image') !== $quiz->image ? null : $quiz->image,
                'updated_at' => now()
            ]);

            $questionsId = [];
            $storageRemovableQuestionImages = [];
            $basePath = "quiz/images/$shop->name/questions";
            foreach ($request->input('questions') ?: [] as $question) {

                $quizQuestion = QuizQuestion::updateOrCreate(
                    [
                        'id' => data_get($question, 'id'),
                        'quiz_id' => $quiz->id,
                        'user_id' => $shop->id,
                    ],
                    [
                        'title' => data_get($question, 'title'),
                        'label' => data_get($question, 'label'),
                        'image' => is_string(data_get($question, 'image')) ? data_get($question, 'image') : null,
                        'order' => data_get($question, 'order') ?: 0,
                    ]);

                $questionOptions = data_get($question, 'options', []);

                if (empty($quizQuestion->image)) {
                    $storageRemovableQuestionImages[] = $quizQuestion->id;
                }

                $optionsId = [];
                $storageNonRemovableOptions = [];
                foreach ($questionOptions as $questionOption) {
                    $option = QuestionOption::updateOrCreate(
                        [
                            'id' => data_get($questionOption, 'id'),
                            'quiz_id' => $quiz->id,
                            'user_id' => $shop->id,
                            'question_id' => $quizQuestion->id
                        ],
                        [
                            'type' => data_get($questionOption, 'type') ?: 'title',
                            'title' => data_get($questionOption, 'type') != 'image' ? data_get($questionOption, 'title') : null,
                            'image' => data_get($questionOption, 'type') == 'image' ? (is_string(data_get($questionOption, 'image')) ? data_get($questionOption, 'image') : null) : null,
                            'description' => data_get($questionOption, 'description'),
                            'correct' => data_get($questionOption, 'correct') ?: 0,
                            'order' => data_get($questionOption, 'order'),
                            'updated_at' => now()
                        ]);

                    $optionsId[] = $option->id;

                    if (data_get($questionOption, 'type') == 'image') {
                        $storageNonRemovableOptions[] = $option->id;
                    }
                }

                $currentDirectories = Storage::disk('public')->allDirectories("$basePath/$quizQuestion->id/options");
                foreach ($currentDirectories as $currentDirectory) {
                    $directoryName = basename($currentDirectory);
                    if (!in_array($directoryName, $storageNonRemovableOptions)) {
                        Storage::disk('public')->deleteDirectory("$basePath/$quizQuestion->id/options/$directoryName");
                    }
                }

                QuestionOption::where('question_id', $quizQuestion->id)->whereNotIn('id', $optionsId)->delete();

                $questionsId[] = $quizQuestion->id;
            }

            $questionDirectories = Storage::disk('public')->allDirectories("$basePath");
            foreach ($questionDirectories as $questionDirectory) {
                $directoryName = basename($questionDirectory);
                if (in_array($directoryName, $storageRemovableQuestionImages)) {
                    Storage::disk('public')->deleteDirectory("$basePath/$directoryName/image");
                }
            }

            QuizQuestion::where('quiz_id', $quiz->id)->whereNotIn('id', $questionsId)->delete();

            $resultsId = [];
            $storageRemovableResults = [];
            $baseResultPath = "quiz/images/$shop->name/results";
            foreach ($request->input('results') ?: [] as $result) {

                $quizResult = QuizResult::updateOrCreate(
                    [
                        'id' => data_get($result, 'id'),
                        'quiz_id' => $quiz->id,
                        'user_id' => $shop->id,
                    ],
                    [
                        'type' => data_get($result, 'type') ?: 'title',
                        'percentage' => data_get($result, 'percentage') ?: 0,
                        'title' => data_get($result, 'title'),
                        'smiley' => data_get($result, 'type') == 'smiley' ? data_get($result, 'smiley') : null,
                        'image' => data_get($result, 'type') == 'image' ? (is_string(data_get($result, 'image')) ? data_get($result, 'image') : null) : null,
                        'description' => data_get($result, 'type') == 'description' || data_get($result, 'type') == 'title' ? data_get($result, 'description') : null,
                        'cta_label' => data_get($result, 'cta_label'),
                        'cta_link' => data_get($result, 'cta_link'),
                        'text_label' => data_get($result, 'text_label'),
                        'text_link' => data_get($result, 'text_link'),
                        'order' => data_get($result, 'order') ?: 0,
                    ]);

                $resultsId[] = $quizResult->id;

                if (data_get($result, 'type') != 'image') {
                    $storageRemovableResults[] = $quizResult->id;
                }
            }

            $currentResultDirectories = Storage::disk('public')->allDirectories("$baseResultPath");
            foreach ($currentResultDirectories as $currentDirectory) {
                $directoryName = basename($currentDirectory);
                if (in_array($directoryName, $storageRemovableResults)) {
                    Storage::disk('public')->deleteDirectory("$baseResultPath/$directoryName");
                }
            }

            QuizResult::where('quiz_id', $quiz->id)->whereNotIn('id', $resultsId)->delete();

            return Quiz::with(['questions.options', 'results'])->findOrFail($quiz->id);
        });

        return response(['quiz' => $quiz, 'message' => 'Quiz updated successfully'], 200);
    }

    public function upload(Request $request, Quiz $quiz) {

        $request->validate([
            'image' => 'sometimes|image',
            'questions.*.options.*' => 'sometimes|image',
            'questions.*.image' => 'sometimes|image',
            'results.*.image' => 'sometimes|image'
        ]);

        try {

            $shop = $request->user();
            $imageLink = $this->manageImage($request, $shop);
            $questionOptionsImageLinks = $this->manageQuestionOptionsImage($request, $shop);
            $resultImages = $this->manageResultImage($request, $shop);

            if ($imageLink) {
                $quiz->update(['image' => $imageLink]);
            }

            if (!empty($questionOptionsImageLinks)) {

                foreach ($questionOptionsImageLinks as $questionKey => $questionOptionsImageLink) {
                    $questionImage = data_get($questionOptionsImageLink, 'image') ?: [];

                    if (!empty($questionImage)) {
                        QuizQuestion::where('id', $questionKey)->update(['image' => $questionImage]);
                    }

                    $options = data_get($questionOptionsImageLink, 'options') ?: [];
                    if (!empty($options)) {
                        foreach ($options as $optionKey => $optionImage) {
                            QuestionOption::where('id', $optionKey)->update(['image' => $optionImage]);
                        }
                    }
                }
            }

            if (!empty($resultImages)) {
                foreach ($resultImages as $resultIndex => $resultImage) {
                    QuizResult::where('id', $resultIndex)->update(['image' => $resultImage]);
                }
            }

            return response(['image' => $imageLink]);

        } catch (\Exception $e) {

            return response(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Quiz $quiz) {
        $quiz->load(['questions.options', 'results']);
        return response(['quiz' => $quiz], 200);
    }

    public function delete(Request $request, Quiz $quiz) {
        $quiz->load(['questions.options', 'results']);
        $shop = $request->user();
        $this->removeImage($shop, $quiz);
        $quiz->delete();

        return response(['message' => 'deleted successfully'], 200);
    }
}
