<?php

namespace App\Http\Controllers;

use App\Models\AllergyTest;
use App\Models\AllergyTestQuestion;
use App\Models\AllergyTestQuestionOption;
use App\Models\AllergyTestResult;
use App\Models\AllergyTestResultCta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllergyTestController extends Controller
{
    public function index(Request $request) {
        $testType = data_get($request, 'type');
        $shop = $request->user();
        $allergyTests = AllergyTest::where([['user_id', $shop->id],['type', $testType]])->paginate(10);
        return response(['allergyTests' => $allergyTests], 200);
    }

    public function store(Request $request) {

        $request->validate([
            'type' => 'required',

            'questions' => 'nullable|array',
            'questions.*.order' => 'required|integer',

            'questions.*.sub_questions.*.order' => 'required|integer',
            'questions.*.options.*.order' => 'required|integer',
//            'questions.*.options.*.weightage' => 'required|integer',
            'questions.*.options.*.value' => 'required|integer',

            'questions.*.options.*.relatedQuestions' => 'nullable|array',
            'questions.*.options.*.relatedQuestions.*.order' => 'required|integer',

            'results' => 'nullable|array',
//            'results.*.percentage_range' => 'required|array',

        ]);
        $shop = $request->user();

        $allergyTest = DB::transaction(function () use ($request, $shop) {
            $allergyTest = AllergyTest::create([
                'uuid' => str()->uuid(),
                'user_id' => $shop->id,
                'type' => $request->input('type') ?: 'allergy-test-self)',
                'title' => $request->input('title') ?: null,
                'label' => $request->input('label') ?: null,
                'description' => $request->input('description') ?: null,
                'cta_label' => $request->input('cta_label') ?: null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if (!empty($request->questions)) {
                foreach ($request->input('questions') ?: [] as $question) {
                    $questionType = data_get($question, 'type');

                    $createdQuestion = AllergyTestQuestion::create([
                        'user_id' => $shop->id,
                        'allergy_test_id' => $allergyTest->id,
                        'type' => $questionType ?: 'questionWithOption',
                        'has_redirect_result' => data_get($question, 'has_redirect_result') ?: 0,
                        'has_redirect_question' => data_get($question, 'has_redirect_question') ?: 0,
                        'title' => data_get($question, 'title'),
                        'description' => data_get($question, 'description'),
                        'order' => data_get($question, 'order') ?: 0,
                        'question_data' => data_get($question, 'question_data'),
                        'redirect_question_data' => data_get($question, 'redirect_question_data'),
                        'redirect_result_data' => data_get($question, 'redirect_result_data')
                    ]);

                    $createdQuestionId = data_get($createdQuestion, 'id');

                    if ($questionType === 'questionWithSubQuestion') {
                        $subQuestions = data_get($question, 'sub_questions') ?: [];

                        $subQuestionsArray = [];
                        foreach ($subQuestions as $subQuestion) {
                            $subQuestionsArray[] = [
                                'user_id' => $shop->id,
                                'allergy_test_id' => $allergyTest->id,
                                'type' => 'questionWithOption',
                                'order' => data_get($subQuestion, 'order') ?: 0,
                                'title' => data_get($subQuestion, 'title'),
                                'parent_id' => $createdQuestionId,
                            ];
                        }
                        AllergyTestQuestion::insert($subQuestionsArray);
                    }

                    $options = data_get($question, 'options') ?: [];
                    if (!empty($options)) {
                        foreach ($options as $option) {
                            $createdOption = AllergyTestQuestionOption::create([
                                'user_id' => $shop->id,
                                'allergy_test_id' => $allergyTest->id,
                                'allergy_test_question_id' => $createdQuestionId,
                                'order' => data_get($option, 'order') ?: 0,
                                'name' => data_get($option, 'name'),
                                'weightage' => data_get($option, 'weightage') ?: 0,
                                'value' => data_get($option, 'value') ?: 0
                            ]);

                            $createdOptionId = data_get($createdOption, 'id');

                            if ($questionType === 'questionWithRelatedOption') {
                                $relatedQuestions = data_get($option, 'related_questions') ?: [];

                                $relatedQuestionsArray = [];
                                foreach ($relatedQuestions as $relatedQuestion) {
                                    $relatedQuestionsArray[] = [
                                        'user_id' => $shop->id,
                                        'allergy_test_id' => $allergyTest->id,
                                        'type' => data_get($relatedQuestion, 'type'),
                                        'title' => data_get($relatedQuestion, 'title'),
                                        'description' => data_get($relatedQuestion, 'description'),
                                        'order' => data_get($relatedQuestion, 'order') ?: 0,
                                        'question_data' => json_encode(data_get($relatedQuestion, 'question_data')),
                                        'related_option_id' => $createdOptionId,
                                    ];
                                }

                                AllergyTestQuestion::insert($relatedQuestionsArray);
                            }
                        }
                    }
                }
            }

            if (!empty($request->results)) {
                foreach ($request->input('results') ?: [] as $result) {
                    $createdResult = AllergyTestResult::create([
                        'user_id' => $shop->id,
                        'allergy_test_id' => $allergyTest->id,
                        'type' => data_get($result, 'type') ?: 'resultSimple',
                        'default' => data_get($result, 'default') ?: 0,
                        'order' => data_get($result, 'order') ?: 0,
                        'percentage_range' => data_get($result, 'percentage_range') ?: [],
                        'label' => data_get($result, 'label'),
                        'title' => data_get($result, 'title'),
                        'description' => data_get($result, 'description'),
                        'instruction' => data_get($result, 'instruction'),
                        'result_data' => data_get($result, 'result_data'),
                    ]);

                    $createdResultId = data_get($createdResult, 'id');

                    $resultCtas = data_get($result, 'result_ctas') ?: [];
                    if (!empty($resultCtas)) {

                        $resultCtasArray = [];
                        foreach ($resultCtas as $resultCta) {
                            $resultCtasArray[] = [
                                'user_id' => $shop->id,
                                'allergy_test_id' => $allergyTest->id,
                                'allergy_test_result_id' => $createdResultId,
                                'text' => data_get($resultCta, 'text'),
                                'link' => data_get($resultCta, 'link'),
                                'target_blank' => data_get($resultCta, 'target_blank') ?: 0,
                            ];
                        }

                        AllergyTestResultCta::insert($resultCtasArray);
                    }
                }
            }
        });

        return response(['allergyTest' => $allergyTest, 'message' => 'Test created successfully'], 200);
    }

    public function update(Request $request, AllergyTest $allergyTest) {

        $request->validate([
            'id' => 'required',
            'type' => 'required',

            'questions' => 'nullable|array',
//            'questions.*.order' => 'required|integer|unique:allergy_test_questions',

            'questions.*.sub_questions.*.order' => 'required|integer',
            'questions.*.options.*.order' => 'required|integer',
//            'questions.*.options.*.weightage' => 'required|integer',
            'questions.*.options.*.value' => 'required|integer',

//            'questions.*.redirect_question_data.*.questionWeightage.*.from' => 'required_if:questions.*.has_redirect_question,true',
//            'questions.*.redirect_question_data.*.questionWeightage.*.to' => 'required_if:questions.*.has_redirect_question,true',
//            'questions.*.redirect_question_data.*.questionOrder' => 'required_if:questions.*.has_redirect_question,true',

            'questions.*.options.*.relatedQuestions' => 'nullable|array',
            'questions.*.options.*.relatedQuestions.*.order' => 'required|integer',

            'results' => 'nullable|array',
//            'results.*.percentage_range' => 'required|array',

        ]);
        $shop = $request->user();

        $allergyTest = DB::transaction(function () use ($request, $shop, $allergyTest) {
            $allergyTest->update([
                'type' => $request->input('type') ?: 'allergy-test-self)',
                'title' => $request->input('title') ?: null,
                'label' => $request->input('label') ?: null,
                'description' => $request->input('description') ?: null,
                'cta_label' => $request->input('cta_label') ?: null,
                'updated_at' => now(),
            ]);

            $questionsId = [];
            foreach ($request->input('questions') ?: [] as $question) {

                $questionType = data_get($question, 'type');
                $allergyTestQuestion = AllergyTestQuestion::updateOrCreate(
                    [
                        'user_id' => $shop->id,
                        'allergy_test_id' => $allergyTest->id,
                        'id' => data_get($question, 'id'),
                    ],
                    [
                        'type' => $questionType ?: 'questionWithOption',
                        'has_redirect_result' => data_get($question, 'has_redirect_result') ?: 0,
                        'has_redirect_question' => data_get($question, 'has_redirect_question') ?: 0,
                        'title' => data_get($question, 'title'),
                        'description' => data_get($question, 'description'),
                        'order' => data_get($question, 'order') ?: 0,
                        'question_data' => data_get($question, 'question_data'),
                        'redirect_question_data' => data_get($question, 'redirect_question_data'),
                        'redirect_result_data' => data_get($question, 'redirect_result_data')
                    ]
                );

                $allergyTestQuestionId = data_get($allergyTestQuestion, 'id');

                if ($questionType === 'questionWithSubQuestion') {
                    $subQuestions = data_get($question, 'sub_questions') ?: [];

                    foreach ($subQuestions as $subQuestion) {
                        $allergyTestSubQuestion = AllergyTestQuestion::updateOrCreate(
                            [
                                'user_id' => $shop->id,
                                'allergy_test_id' => $allergyTest->id,
                                'parent_id' => $allergyTestQuestionId,
                                'id' => data_get($subQuestion, 'id')
                            ],
                            [
                                'type' => 'questionWithOption',
                                'order' => data_get($subQuestion, 'order') ?: 0,
                                'title' => data_get($subQuestion, 'title'),
                            ]
                        );

                        $questionsId[] = $allergyTestSubQuestion->id;
                    }
                }

                $optionsId = [];
                $options = data_get($question, 'options') ?: [];
                if (!empty($options)) {
                    foreach ($options as $option) {
                        $createdOption = AllergyTestQuestionOption::updateOrcreate(
                            [
                                'user_id' => $shop->id,
                                'allergy_test_id' => $allergyTest->id,
                                'allergy_test_question_id' => $allergyTestQuestionId,
                                'id' => data_get($option, 'id')
                            ],
                            [
                                'order' => data_get($option, 'order') ?: 0,
                                'name' => data_get($option, 'name'),
                                'weightage' => data_get($option, 'weightage') ?: 0,
                                'value' => data_get($option, 'value') ?: 0
                            ]
                        );

                        $createdOptionId = data_get($createdOption, 'id');

                        if ($questionType === 'questionWithRelatedOption') {
                            $relatedQuestions = data_get($option, 'related_questions') ?: [];

                            foreach ($relatedQuestions as $relatedQuestion) {
                                $updatedRelatedQuestion = AllergyTestQuestion::updateOrCreate(
                                    [
                                        'user_id' => $shop->id,
                                        'allergy_test_id' => $allergyTest->id,
                                        'related_option_id' => $createdOptionId,
                                        'id' => data_get($relatedQuestion, 'id')
                                    ],
                                    [
                                        'type' => data_get($relatedQuestion, 'type'),
                                        'title' => data_get($relatedQuestion, 'title'),
                                        'description' => data_get($relatedQuestion, 'description'),
                                        'order' => data_get($relatedQuestion, 'order') ?: 0,
                                        'question_data' => data_get($relatedQuestion, 'question_data'),
                                    ]
                                );

                                $questionsId[] = $updatedRelatedQuestion->id;
                            }
                        }

                        $optionsId[] = $createdOption->id;
                    }
                }

                AllergyTestQuestionOption::where('allergy_test_question_id', $allergyTestQuestionId)->whereNotIn('id', $optionsId)->delete();
                $questionsId[] = $allergyTestQuestionId;
            }

            AllergyTestQuestion::where('allergy_test_id', $allergyTest->id)->whereNotIn('id', $questionsId)->delete();

            $resultIds = [];
            if (!empty($request->results)) {
                foreach ($request->input('results') ?: [] as $result) {
                    $updatedResult = AllergyTestResult::updateOrcreate(
                        [
                            'user_id' => $shop->id,
                            'allergy_test_id' => $allergyTest->id,
                            'id' => data_get($result, 'id')
                        ],
                        [
                            'type' => data_get($result, 'type') ?: 'resultSimple',
                            'default' => data_get($result, 'default') ?: 0,
                            'order' => data_get($result, 'order') ?: 0,
                            'percentage_range' => data_get($result, 'percentage_range') ?: [],
                            'label' => data_get($result, 'label'),
                            'title' => data_get($result, 'title'),
                            'description' => data_get($result, 'description'),
                            'instruction' => data_get($result, 'instruction'),
                            'result_data' => data_get($result, 'result_data'),
                    ]);

                    $updatedResultId = data_get($updatedResult, 'id');

                    $resultCtas = data_get($result, 'result_ctas') ?: [];
                    if (!empty($resultCtas)) {

                        $resultCtasId = [];
                        foreach ($resultCtas as $resultCta) {
                            $updatedResultCta = AllergyTestResultCta::updateOrCreate(
                                [
                                    'user_id' => $shop->id,
                                    'allergy_test_id' => $allergyTest->id,
                                    'allergy_test_result_id' => $updatedResultId,
                                    'id' => data_get($resultCta, 'id')
                                ],
                                [
                                    'text' => data_get($resultCta, 'text'),
                                    'link' => data_get($resultCta, 'link'),
                                    'target_blank' => data_get($resultCta, 'target_blank') ?: 0,
                                ]
                            );

                            $resultCtasId[] = $updatedResultCta->id;
                        }

                        AllergyTestResultCta::where('allergy_test_result_id', $updatedResultId)->whereNotIn('id', $resultCtasId)->delete();
                    }
                    $resultIds[] = $updatedResultId;
                }
                AllergyTestResult::where('allergy_test_id', $allergyTest->id)->whereNotIn('id', $resultIds)->delete();
            }
        });

        return response(['allergyTest' => $allergyTest, 'message' => 'Test updated successfully'], 200);
    }

    public function show(AllergyTest $allergyTest) {
        $allergyTest->load(['questions.options.relatedQuestions', 'results.result_ctas', 'questions.subQuestions']);
        return response(['allergyTest' => $allergyTest], 200);
    }

    public function delete(AllergyTest $allergyTest) {
        $allergyTest->load(['questions.options.relatedQuestions', 'results.result_ctas', 'questions.subQuestions']);
        $allergyTest->delete();
        return response(['message' => 'Test Deleted Successfully'], 200);
    }
}
