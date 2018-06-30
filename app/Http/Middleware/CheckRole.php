<?php

namespace App\Http\Middleware;

use Closure;

class CheckRole
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle($request, Closure $next, $role1, $role2)
    {
        if (!($request->user()->hasRole($role1)|| $request->user()->hasRole($role2))) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}