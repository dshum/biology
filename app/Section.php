<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\ElementInterface;
use Moonlight\Main\ElementTrait;
use Illuminate\Support\Facades\URL;

class Section extends Model implements ElementInterface
{
    use ElementTrait;

    public function getHref()
	{
		return '/'.$this->url;
	}
}
