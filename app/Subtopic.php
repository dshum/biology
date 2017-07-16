<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subtopic extends Model
{
    use SoftDeletes;

    public function topic()
	{
		return $this->belongsTo('App\Topic', 'topic_id');
	}
}
