<?php

namespace Cuakx\Core\Http\Middleware;

use Closure;
use Cuakx\Core\Utils\Auth\Session\AuthenticationUtil;
use Cuakx\Core\Utils\Auth\Session\Exception\UnauthorizedException;
use Exception;

class AuthCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check whether there is Authorization exist or not
        if(!$request->header("Authorization")){
            throw new UnauthorizedException();
        }

        try {
            // Check whether the token is valid or not
            (new AuthenticationUtil())->getCacheSessionByToken($request->header("Authorization"));
        }catch(Exception $e){
            throw new UnauthorizedException();
        }

        return $next($request);
    }
}