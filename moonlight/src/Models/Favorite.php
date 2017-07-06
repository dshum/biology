<?php 

namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\Element;

class Favorite extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_favorites';

	public static function boot()
	{
		parent::boot();

		static::created(function($element) {
			$element->flush();
		});

		static::saved(function($element) {
			$element->flush();
		});

		static::deleted(function($element) {
			$element->flush();
		});
    }

	public function flush()
	{
		//\Cache::tags('Favorite')->flush();
	}

	public function getElement()
	{
		if ( ! $this->class_id) return null;

		return Element::getByClassId($this->class_id);
	}

}
