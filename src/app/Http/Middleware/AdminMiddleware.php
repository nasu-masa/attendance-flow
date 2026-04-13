<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth('admin')->check()) {
            return $next($request);
        }

        $user = auth('admin')->user();

        if ($user->role !== User::ROLE_ADMIN) {

            logger()->warning('Role mismatch: non-admin user attempted to access admin route', [
                'user_id' => $user?->id,
                'route'   => $request->path(),
                'ip'      => $request->ip(),
            ]);

            abort(403);
        }

        return $next($request);
    }
}
