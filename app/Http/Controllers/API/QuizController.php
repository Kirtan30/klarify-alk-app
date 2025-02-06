<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use Illuminate\Http\Request;
use App\Http\Controllers\QuizController as AppQuizController;

class QuizController extends Controller
{
    public $appQuizController;

    public function __construct() {
        $this->appQuizController = new AppQuizController();
    }

    public function show(Quiz $quiz) {
        return $this->appQuizController->show($quiz);
    }
}
