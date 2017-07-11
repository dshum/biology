<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;

class Answer extends Model implements ElementInterface
{
    use ElementTrait;

    public function question()
    {
        return $this->belongsTo('App\Question');
    }
}
