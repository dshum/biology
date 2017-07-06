<?php namespace Moonlight\Models;

use Illuminate\Database\Eloquent\Model;
use Moonlight\Main\LoggedUser;
use Moonlight\Main\UserActionType;

class UserAction extends Model {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'admin_user_actions';

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
		//\Cache::tags('UserAction')->flush();
	}

	public function user()
	{
		return $this->belongsTo('Moonlight\Models\User');
	}

	public function getActionTypeName()
	{
		return UserActionType::getActionTypeName($this->action_type_id);
	}

	public static function log($actionTypeId, $comments)
	{
		$loggedUser = LoggedUser::getUser();

        $method = isset($_SERVER['REQUEST_METHOD'])
            ? $_SERVER['REQUEST_METHOD'] : '';

        $uri = isset($_SERVER['REQUEST_URI'])
            ? $_SERVER['REQUEST_URI'] : '';

        $userAction = new UserAction;

        $userAction->user_id = $loggedUser->id;
        $userAction->action_type_id = $actionTypeId;
        $userAction->comments = $comments;
        $userAction->url = $method.' '.$uri;

        $userAction->save();
	}
}
