<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {

                if ($request->routeIs('login') || $request->routeIs('admin.login')) {
                    $user = Auth::guard($guard)->user();

                    return $user->role === User::ROLE_ADMIN
                        ? redirect('/admin/attendance/list')
                        : redirect('/attendance');
                }
            }
        }

        return $next($request);
    }
}
