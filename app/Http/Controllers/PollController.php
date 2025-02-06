<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollAnswer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public function index(Request $request) {
        $shop = $request->user();
        $perPage = data_get($request,'perPage', 10);
        $searchValue = data_get($request, 'searchValue');
        $startDate = data_get($request, 'startDate');
        $endDate = data_get($request, 'endDate');

        if (!empty($startDate) && !empty($endDate)) {
            $startDate = Carbon::parse($startDate)->format('y-m-d');
            $endDate = Carbon::parse($endDate)->format('y-m-d');
        }

        $polls = Poll::with(['answers.responses' => function ($query) use ($startDate, $endDate) {
            if (!empty($startDate) && !empty($endDate)) {
                $query->whereDate('updated_at', '>=', $startDate)->whereDate('updated_at', '<=', $endDate);
            }
        }, 'responses' => function ($query) use ($startDate, $endDate) {
            if (!empty($startDate) && !empty($endDate)) {
                $query->whereDate('updated_at', '>=', $startDate)->whereDate('updated_at', '<=', $endDate);
            }
        }]);

        $polls = $polls->where('user_id', $shop->id);

        if (!empty($startDate) && !empty($endDate)) {
            $polls = $polls->whereHas('responses', function (Builder $query) use ($startDate, $endDate) {
                $query->whereDate('updated_at', '>=', $startDate)->whereDate('updated_at', '<=', $endDate);
            });
        }

        $polls = !empty($searchValue) ?  $polls->whereRaw("title LIKE '%$searchValue%'") : $polls;
        $polls = $polls->paginate($perPage);

        $polls->map(function ($poll) {
           $poll->totalCount = $poll->responses->count();
           $totalCount = $poll->totalCount;

            $poll->answers->map(function ($pollAnswer) use ($totalCount) {
                $pollAnswer->response_count = $pollAnswer->responses->count();
                $pollAnswer->percentage = $totalCount ? round(($pollAnswer->response_count/$totalCount) * 100, 2) : 0;
                unset($pollAnswer['responses']);
                return $pollAnswer;
            });

            unset($poll['responses']);
        });

        return response(['polls' => $polls], 200);
    }

    public function store(Request $request) {

        $request->validate([
            'title' => 'required',
            'label' => 'required',
            'question' => 'required',

            'answers' => 'nullable|array',
            'answers.*.title' => 'required',
        ]);

        $shop = $request->user();

        try {
            $poll = DB::transaction(function () use ($request, $shop) {
                $poll = Poll::create([
                    'uuid' => str()->uuid(),
                    'user_id' => $shop->id,
                    'title' => $request->input('title'),
                    'label' => $request->input('label') ?: null,
                    'question' => $request->input('question'),
                    'description' => $request->input('description') ?: null,
                    'disclaimer' => $request->input('disclaimer') ?: null,
                ]);

                if (!empty($request->answers)) {
                    $answers = [];
                    foreach ($request->input('answers') ?: [] as $answer) {
                        $answers[] = [
                            'user_id' => $shop->id,
                            'poll_id' => $poll->id,
                            'title' => data_get($answer, 'title'),
                            'description' => data_get($answer, 'description'),
                            'cta_label' => data_get($answer, 'cta_label'),
                            'cta_link' => data_get($answer, 'cta_link'),
                            'order' => data_get($answer, 'order') ?: 0,
                            'created_at' => now(),
                            'updated_at' => now()
                        ];
                    }

                    PollAnswer::insert($answers);
                }

                return $poll;
            });

            return response(['poll' => $poll, 'message' => 'poll created successfully'], 200);
        } catch (\Exception $e) {

            return response(['message' => $e->getMessage(), 500]);
        }

    }

    public function update(Request $request, Poll $poll) {
        $request->validate([
            'id' => 'required',
            'title' => 'required',
            'question' => 'required',

            'answers' => 'nullable|array',
            'answers.*.title' => 'required',
        ]);

        $shop = $request->user();

        $poll = DB::transaction(function () use ($request, $shop, $poll) {
            $poll->update([
                'user_id' => $shop->id,
                'title' => $request->input('title'),
                'label' => $request->input('label') ?: null,
                'question' => $request->input('question'),
                'description' => $request->input('description') ?: null,
                'disclaimer' => $request->input('disclaimer') ?: null,
            ]);

            $answersId = [];
            foreach ($request->input('answers') ?: [] as $answer) {
                $pollAnswer = PollAnswer::updateOrCreate(
                    [
                        'id' => data_get($answer, 'id'),
                        'user_id' => $shop->id,
                        'poll_id' => $poll->id,
                    ],
                    [
                        'title' => data_get($answer, 'title'),
                        'description' => data_get($answer, 'description'),
                        'cta_label' => data_get($answer, 'cta_label'),
                        'cta_link' => data_get($answer, 'cta_link'),
                        'order' => data_get($answer, 'order') ?: 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                $answersId[] = $pollAnswer->id;
            }

            PollAnswer::where('poll_id', $poll->id)->whereNotIn('id', $answersId)->delete();

            return $poll;
        });

        return response(['poll' => $poll, 'message' => 'Poll updated successfully'], 200);
    }

    public function show(Poll $poll) {
        $poll->load('answers.responses');
        return response(['poll' => $poll], 200);
    }

    public function delete(Poll $poll) {
        $poll->delete();
        return response(['message' => 'deleted successfully'], 200);
    }
}
