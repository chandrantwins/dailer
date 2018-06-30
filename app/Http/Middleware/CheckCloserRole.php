<?php

namespace App\Http\Middleware;

use Closure;

class CheckCloserRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role1)
    {
        if (!($request->user()->hasRole($role1))) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}