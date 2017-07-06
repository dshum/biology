<?php 

namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteRubric extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_favorite_rubrics';

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
		//\Cache::tags('FavoriteRubric')->flush();
	}
}
