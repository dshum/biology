<?php 

namespace Moonlight\Main;

use Session;
use Carbon\Carbon;
use Moonlight\Models\User;

class LoggedUser 
{
	private static $user = null;

	public static function create()
	{
		return new self();
	}
    
    public static function login(User $user)
    {
        Session::put('logged', $user->id);
        
        $user->last_login = Carbon::now();
        $user->save();
        
        static::setUser($user);
    }
    
    public static function logout()
    {
        Session::forget('logged');
        
        static::dropUser();
    }

	public static function setUser(User $user)
	{
		static::$user = $user;
	}
    
    public static function dropUser()
	{
		static::$user = null;
	}

	public static function getUser()
	{
		return static::$user;
	}

	public static function isLogged()
	{
        return static::getUser() ? true : false;
	}
}
