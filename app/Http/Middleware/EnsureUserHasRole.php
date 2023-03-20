<?php

namespace App\Http\Middleware;

use App\Enums\ResponseStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!$request->user()->hasRole($role)) {
            abort(ResponseStatus::UNAUTHORIZED->value, 'You are not ' . $role);
        }

        return $next($request);
    }
}
