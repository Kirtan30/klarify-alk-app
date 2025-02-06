<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\PollController as AppPollController;
use App\Models\Poll;
use App\Models\PollResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PollController extends Controller
{
    public $appPollController;

    public function __construct() {
        $this->appPollController = new AppPollController();
    }

    public function show(Poll $poll) {
        return $this->appPollController->show($poll);
    }

    public function updateCount(Request $request, Poll $poll) {
        $request->validate([
            'answer' => 'required|exists:poll_answers,id' // Answer id
        ]);


        $answer = $poll->answers()->where('id', $request->input('answer'))->first();
        $answer->count = $answer->count + 1;
        $answer->save();

        PollResponse::create([
            'user_id' => $poll->user_id,
            'poll_id' => $poll->id,
            'poll_answer_id' => $answer->id,
        ]);

        $totalCount = $poll->answers->pluck('count')->sum();

        $poll->answers->map(function ($pollAnswer) use ($totalCount) {
            $pollAnswer->percentage = $totalCount ? round(($pollAnswer->count/$totalCount) * 100, 2) : 0;
            return $pollAnswer;
        });

        return response(['answers' => $poll->answers]);
    }
}
