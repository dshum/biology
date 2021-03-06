<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Topic extends Model
{
    use SoftDeletes;

	public function subject()
	{
		return $this->belongsTo('App\Subject', 'subject_id');
	}
}
