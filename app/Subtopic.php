<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;

class Subtopic extends Model implements ElementInterface
{
    use ElementTrait;

    public function topic()
	{
		return $this->belongsTo('App\Topic', 'topic_id');
	}
}
