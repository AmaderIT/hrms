<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class CheckStatus
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->check() AND auth()->user()->status !== User::STATUS_ACTIVE)
        {
            auth()->logout();
            abort(401, "You are deactivated. Please contact with admin@byslglobal.com");
        }

        return $next($request);
    }
}
