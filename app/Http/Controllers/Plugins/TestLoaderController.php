<?php 

namespace App\Http\Controllers\Plugins;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Validator;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Topic;
use App\Test;
use App\Question;
use App\Answer;

class TestLoaderController extends Controller {

    public function load(Request $request)
	{
		$scope = [];

        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'topic' => 'required|exists:topics,id',
            'content' => 'required',
        ], [
            'title.required' => 'Введите название теста',
            'title.max' => 'Слишком длинное название теста',
            'topic.required' => 'Укажите тему',
            'topic.exists' => 'Некорректный идентификатор темы',
            'content.required' => 'Введите вопросы и ответы',
        ]);
        
        if ($validator->fails()) {
            $messages = $validator->errors();
            
            foreach ([
                'title',
                'topic',
                'content',
            ] as $field) {
                if ($messages->has($field)) {
                    $scope['errors'][] = [
                        'name' => $field,
                        'message' => $messages->first($field)
                    ];
                }
            }
        }
        
        if (isset($scope['errors'])) {
            return response()->json($scope);
        }

        $title = $request->input('title');
        $topic = $request->input('topic');
        $content = $request->input('content');

        $test = new Test;

        $test->name = $title;
        $test->topic_id = $topic;

        try {
			$maxOrder = $test->max('order');
			$test->order = (int)$maxOrder + 1;
		} catch (\Exception $e) {
			$test->order = 1;
		}

        $test->save();

        $questionParts = explode("\n\n\n", $content);

        $i = 1;

        foreach ($questionParts as $questionPart) {
            try {
                list($questionText, $answerParts) = explode("\n\n", $questionPart);
            } catch (BaseException $e) {
                continue;
            }

            $questionText = trim($questionText);

            if ( ! $questionText) continue;

            $questionText = '<p>'.$questionText.'</p>';

            $answers = explode("\n", $answerParts);

            foreach ($answers as $k => $answerText) {
                $answerText = trim($answerText);
                $answerText = trim($answerText, ',;.');
                if ($answerText) {
                    $answers[$k] = $answerText;
                } else {
                    unset($answers[$k]);
                }
            }

            if (empty($answers)) continue;

            if (sizeof($answers) <= 4) {
                $mark = 1;
                $questionTypeId = 1;
            } else {
                $mark = 2;
                $questionTypeId = 2;
            }

            $question = new Question;

            $question->name = 'Вопрос '.$i;
            $question->order = $i;
            $question->question = $questionText;
            $question->mark = $mark;
            $question->topic_id = $topic;
            $question->question_type_id = $questionTypeId;

            $question->save();

            $question->tests()->attach($test->id);

            $j = 1;

            foreach ($answers as $k => $answerText) {
                $answer = new Answer;

                $answer->name = 'Ответ '.$j;
                $answer->order = $j;
                $answer->answer = $answerText;
                $answer->question_id = $question->id;

                $answer->save();

                $j++;
            }

            $i++;
        }

        $scope['ok'] = true;

		return response()->json($scope);
	}

	public function index()
	{
		$scope = [];

		$topics = Topic::orderBy('order')->get();

        $scope['topics'] = $topics;

		return response()->json($scope);
	}

} 