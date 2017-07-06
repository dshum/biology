<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;

class QuestionType extends Model implements ElementInterface
{
    use ElementTrait;
}
