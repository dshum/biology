<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Question extends Model
{
    use SoftDeletes;

    /**
	 * The Eloquent test model.
	 *
	 * @var string
	 */
	public static $testModel = 'App\Test';

	/**
	 * The tests-questions pivot table name.
	 *
	 * @var string
	 */
	public static $testsQuestionsPivot = 'tests_questions_pivot';
    
    public function tests()
	{
		return $this->belongsToMany(static::$testModel, static::$testsQuestionsPivot);
	}

	public function answers()
    {
        return $this->hasMany('App\Answer');
    }

    public function getAnswersInfo()
    {
        $scope = [];

        $scope['answers'] = [];

        $answers = $this->answers()->orderBy('order')->get();

        foreach ($answers as $answer) {
            $scope['answers'][] = [
                'id' => $answer->id,
                'answer' => $answer->answer,
                'correct' => $answer->correct,
            ];
        }

        return $scope;
    }
}
