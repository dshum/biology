<?php

namespace Moonlight\Middleware;

use Log;
use Closure;
use Moonlight\Main\LoggedUser;
use Moonlight\Models\User;
use Moonlight\Utils\UserJwtCodec;

class AuthMiddleware
{
    const test = false;
    
    public function handle($request, Closure $next)
    {   
        if (self::test) {
            $user = User::find(2);
        } else {
            $requestHeaders = function_exists('apache_request_headers')
                ? apache_request_headers()
                : $this->getHeaders();
            
            foreach ($requestHeaders as $key => $header) {
                if (strtolower($key) == 'authorization') {
                    unset($key);
                    $requestHeaders['Authorization'] = $header;
                }
            }

            $authorizationHeader = isset($requestHeaders['Authorization'])
                ? $requestHeaders['Authorization'] : null;

            if ( ! $authorizationHeader) {
                $scope['message'] = "No auth header";
                return response()->json($scope, 401);
            }

            $token = str_replace('Bearer ', '', $authorizationHeader);

            $userCodec = app(UserJwtCodec::class);

            $user = $userCodec->decode($token);

            if ( ! $user) {
                $scope['message'] = "Invalid token";
                return response()->json($scope, 401);
            }
        }

        LoggedUser::setUser($user);

        return $next($request);
    }
    
    private function getHeaders()
    {
        $arh = [];
        
        $rx_http = '/\AHTTP_/';
        
        foreach ($_SERVER as $key => $val) {
            if (preg_match($rx_http, $key)) {
                $arh_key = preg_replace($rx_http, '', $key);
                $rx_matches = explode('_', $arh_key);
                
                if (sizeof($rx_matches) > 0 and strlen($arh_key) > 2) {
                    foreach ($rx_matches as $ak_key => $ak_val) {
                        $rx_matches[$ak_key] = ucfirst($ak_val);
                    }
                    
                    $arh_key = implode('-', $rx_matches);
                }
                
                $arh[$arh_key] = $val;
            }
        }
        
        return $arh;
    }
}