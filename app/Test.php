<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;

class Test extends Model implements ElementInterface
{
    use ElementTrait;

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
