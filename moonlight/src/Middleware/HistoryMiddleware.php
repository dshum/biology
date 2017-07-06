<?php

namespace Moonlight\Middleware;

use Closure;
use Moonlight\Main\LoggedUser;

class HistoryMiddleware
{
    public function handle($request, Closure $next)
    {   
        $loggedUser = LoggedUser::getUser();
        
        $history = $loggedUser->getParameter('history');
        
        $history = config('app.url').$request->getRequestUri();
        
        $loggedUser->setParameter('history', $history);

        return $next($request);
    }
}