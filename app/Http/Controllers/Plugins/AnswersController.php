<?php 

namespace App\Http\Controllers\Plugins;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use Moonlight\Main\Element;
use App\Http\Controllers\Controller;
use App\Question;
use App\Answer;

class AnswersController extends Controller {

    public function setCorrect(Request $request, $answerId)
	{
		$scope = [];

        $currentAnswer = Answer::find((int)$answerId);

        if (! $currentAnswer) {
            $scope['error'] = 'Ответ не найден.';

            return response()->json($scope);
        }

        $question = $currentAnswer->question;

        $answers = $question->answers()->orderBy('order')->get();

        foreach ($answers as $answer) {
            $answer->correct = $answer->id === $currentAnswer->id
                ? true : false;
            $answer->save();
        }

        $scope['answers'] = [];

        foreach ($answers as $answer) {
            $scope['answers'][] = [
                'id' => $answer->id,
                'classId' => Element::getClassId($answer),
                'answer' => $answer->answer,
                'correct' => $answer->correct,
            ];
        }

		return response()->json($scope);
	}

	public function index(Request $request, $questionId)
	{
		$scope = [];

        $question = Question::find((int)$questionId);

        if (! $question) {
            $scope['error'] = 'Вопрос не найден.';

            return response()->json($scope);
        }

        $scope['answers'] = [];

		$answers = $question->answers()->orderBy('order')->get();

        foreach ($answers as $answer) {
            $scope['answers'][] = [
                'id' => $answer->id,
                'classId' => Element::getClassId($answer),
                'answer' => $answer->answer,
                'correct' => $answer->correct,
            ];
        }

		return response()->json($scope);
	}

} 