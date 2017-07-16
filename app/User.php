<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use SoftDeletes;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getDates()
	{
		return array(
            'birthday',
            'last_date',
            'previous_date',
			'created_at',
			'updated_at',
			'deleted_at',
		);
	}
    
    public function getRemotedPhoto()
    {
        return $this->getProperty('photo')->exists()
            ? '<img src="'.$this->getProperty('photo')->src().'" height="100">'
            : null;
    }
    
    public function getVkUrl()
    {
        $screenName = $this->vk_screen_name
            ? $this->vk_screen_name
            : 'id'.$this->vk_id;

        return $this->social == 'vk'
            ? '<a href="https://vk.com/'.$screenName.'" target="_blank">https://vk.com/'.$screenName.'</a>'
            : null;
    }

}
