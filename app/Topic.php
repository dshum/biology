<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;

class Topic extends Model implements ElementInterface
{
    use ElementTrait;

	public function subject()
	{
		return $this->belongsTo('App\Subject', 'subject_id');
	}
}
