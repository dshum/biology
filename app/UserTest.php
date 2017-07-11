<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;

class UserTest extends Model implements ElementInterface
{
    use ElementTrait;

    public function test()
    {
        return $this->belongsTo('App\Test');
    }
}
