<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use SoftDeletes;

    /**
	 * The Eloquent question model.
	 *
	 * @var string
	 */
	public static $questionModel = 'App\Question';

	/**
	 * The tests-questions pivot table name.
	 *
	 * @var string
	 */
	public static $testsQuestionsPivot = 'tests_questions_pivot';
    
    public function questions()
	{
		return $this->belongsToMany(static::$questionModel, static::$testsQuestionsPivot);
	}
}
