<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Test;
use App\Question;
use App\Answer;
use App\UserTest;
use App\UserQuestion;
use App\UserAnswer;
use Log;

class TestController extends Controller {

	public function __construct()
	{
		$this->middleware('auth');
	}

	public function save(Request $request, $id)
	{
		$scope = [];

		$user = Auth::user();

        $test = Test::where('id', $id)->first();

        if (! $test) {
            return redirect()->route('welcome');
        }

		$userTest = UserTest::where('test_id', $test->id)->
			where('user_id', $user->id)->
			first();

		if (! $userTest) {
			$userTest = new UserTest;

			$userTest->name = $test->name;
			$userTest->user_id = $user->id;
			$userTest->test_id = $test->id;

			$userTest->save();
		}

		$answers = $request->input('answers');

		if (empty($answers)) {
			return redirect()->route('welcome');
		}

		foreach ($answers as $questionId => $answerId) {
			$question = Question::where('id', $questionId)->first();
			$answer = Answer::where('id', $answerId)->first();

			if (! $question) continue;
			if (! $answer) continue;

			$userQuestion = UserQuestion::where('question_id', $question->id)->first();

			if (! $userQuestion) {
				$userQuestion = new UserQuestion;

				$userQuestion->user_test_id = $userTest->id;
				$userQuestion->question_id = $question->id;
			}

			if ($answer->correct) {
				$userQuestion->correct = true;
			}

			$userQuestion->name = $question->name;

			$userQuestion->save();

			$userAnswer = UserAnswer::where('user_question_id', $userQuestion->id)->first();

			if (! $userAnswer) {
				$userAnswer = new UserAnswer;

				$userAnswer->user_question_id = $userQuestion->id;
			} elseif ($userAnswer->answer_id !== $answer->id) {
				$userAnswer->forceDelete();

				$userAnswer = new UserAnswer;

				$userAnswer->user_question_id = $userQuestion->id;
			}

			$userAnswer->name = $answer->name;
			$userAnswer->answer_id = $answer->id;

			$userAnswer->save();
		}

		$questionsCount = $test->questions()->count();
		$userQuestionsCount = UserQuestion::where('user_test_id', $userTest->id)->count();

		if ($questionsCount === $userQuestionsCount) {
			$userTest->complete = true;

			$userTest->save();
		}

		return redirect()->route('home');
	}

	public function index(Request $request, $id)
	{
		$scope = [];

		$user = Auth::user();

        $test = Test::where('id', $id)->first();

        if (! $test) {
            return redirect()->route('welcome');
        }

		$questionAnswered = [];
		$answerChecked = [];

		$userTest = UserTest::where('test_id', $test->id)->
			where('user_id', $user->id)->
			first();

		if ($userTest) {
			$userQuestions = UserQuestion::where('user_test_id', $userTest->id)->get();

			$userQuestinIds = [];

			foreach ($userQuestions as $userQuestion) {
				$userQuestinIds[] = $userQuestion->id;
				$questionAnswered[$userQuestion->question_id] = $userQuestion;
			}

			$userAnswers = UserAnswer::whereIn('user_question_id', $userQuestinIds)->get();

			foreach ($userAnswers as $userAnswer) {
				$answerChecked[$userAnswer->answer_id] = true;
			}
		}

		$questions = $test->questions()->
			orderBy('order')->
			get();

		$questinIds = [];

		foreach ($questions as $question) {
			$questinIds[] = $question->id;
		}

		$answers = Answer::whereIn('question_id', $questinIds)->
			orderBy('order')->
			get();

		$scope['userTest'] = $userTest;
		$scope['questionAnswered'] = $questionAnswered;
		$scope['answerChecked'] = $answerChecked;
        $scope['test'] = $test;
		$scope['questions'] = $questions;
		$scope['answers'] = $answers;

		return view('test', $scope);
	}

} 