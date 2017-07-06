<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;

class Question extends Model implements ElementInterface
{
    use ElementTrait;

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
}
