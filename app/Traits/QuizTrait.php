<?php


namespace App\Traits;


use Illuminate\Support\Facades\Storage;

trait QuizTrait
{
    public function manageImage($request, $shop) {

        $imageLink = null;
        $basePath = "quiz/images/$shop->name";

        if ($request->hasFile('image')) {

            $image = $request->file('image');
            $fileName = rand(111111, 999999) . '_' . $image->getClientOriginalName();
            Storage::disk('public')->putFileAs($basePath, $image, $fileName);
            $imageLink = url("storage/$basePath/$fileName");

        }

        return $imageLink;
    }

    public function manageQuestionOptionsImage($request, $shop) {

        $queOptionImageLink = [];
        $basePath = "quiz/images/$shop->name/questions";
        $questions = $request->file('questions') ?: [];

        foreach ($questions as $questionKey => $question) {
            try {
                $questionImage = data_get($question, 'image');

                if (!empty($questionImage)) {
                    $queFileName = rand(111111, 999999) . '_' . $questionImage->getClientOriginalName();

                    if (Storage::disk('public')->exists("$basePath/$questionKey/image")) {
                        Storage::disk('public')->deleteDirectory("$basePath/$questionKey/image");
                    }

                    Storage::disk('public')->putFileAs("$basePath/$questionKey/image", $questionImage, $queFileName);
                    $queOptionImageLink[$questionKey]['image'] = url("storage/$basePath/$questionKey/image/$queFileName");
                }

                $options = data_get($question, 'options') ?: [];

                foreach ($options as $optionKey => $option) {
                    $fileName = rand(111111, 999999) . '_' . $option->getClientOriginalName();

                    if (Storage::disk('public')->exists("$basePath/$questionKey/options/$optionKey")) {
                        Storage::disk('public')->deleteDirectory("$basePath/$questionKey/options/$optionKey");
                    }

                    Storage::disk('public')->putFileAs("$basePath/$questionKey/options/$optionKey", $option, $fileName);
                    $queOptionImageLink[$questionKey]['options'][$optionKey] = url("storage/$basePath/$questionKey/options/$optionKey/$fileName");
                }
            } catch (\Exception $e) {

            }
        }

        return $queOptionImageLink;
    }

    public function manageResultImage($request, $shop) {

        $resultLink = [];
        $basePath = "quiz/images/$shop->name/results";
        $results = $request->file('results') ?: [];

        foreach ($results as $resultIndex => $result) {
            try {
                $fileName = rand(111111, 999999) . '_' . $result->getClientOriginalName();

                if (Storage::disk('public')->exists("$basePath/$resultIndex")) {
                    Storage::disk('public')->deleteDirectory("$basePath/$resultIndex");
                }

                Storage::disk('public')->putFileAs("$basePath/$resultIndex", $result, $fileName);
                $resultLink[$resultIndex] = url("storage/$basePath/$resultIndex/$fileName");
            } catch (\Exception $e) {

            }
        }

        return $resultLink;
    }

    public function removeImage($shop, $quiz) {

        $basePath = "quiz/images/$shop->name";

        $quizQuestions  = data_get($quiz, 'questions');
        $quizResults  = data_get($quiz, 'results');

        if ($quiz && $quiz->image) {
            $path = parse_url($quiz->image, PHP_URL_PATH);
            $path = "$basePath/" . basename($path);
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if (!empty($quizQuestions)) {
            $removableQuestionIds = $quizQuestions->pluck('id')->toArray();

            foreach ($removableQuestionIds as $removableQuestionId) {
                $path = "$basePath/questions/$removableQuestionId";
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->deleteDirectory($path);
                }
            }
        }

        if (!empty($quizResults)) {
            $removableResultIds = $quizResults->pluck('id')->toArray();

            foreach ($removableResultIds as $removableResultId) {
                $path = "$basePath/results/$removableResultId";
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->deleteDirectory($path);
                }
            }
        }
    }
}
