<?php

namespace Moonlight\Middleware;

use Log;
use Closure;
use Session;
use Moonlight\Main\LoggedUser;
use Moonlight\Models\User;

class GuestMiddleware
{
    public function handle($request, Closure $next)
    {   
        if (Session::get('logged')) {
            $id = Session::get('logged');
            $user = User::find($id);
            
            if ($user) {
                return redirect()->route('home');
            }
        }

        return $next($request);
    }
}