<?php

namespace Cuakx\Core\Http\Middleware;

use Closure;
use Cuakx\Core\Utils\Auth\Session\AuthenticationUtil;
use Cuakx\Core\Utils\Auth\Session\Exception\UnauthorizedException;
use Cuakx\Core\Utils\Auth\XToken\Exception\InvalidXTokenException;
use Cuakx\Core\Utils\Auth\XToken\XTokenUtil;
use Exception;

class XTokenChecker
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
        if(!$request->header("X-TOKEN")){
            throw new InvalidXTokenException();
        }

        // Check whether the token is valid or not
        if(XTokenUtil::validateToken($request->header("X-TOKEN"), $request)){
            throw new InvalidXTokenException();
        }

        return $next($request);
    }
}