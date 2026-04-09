<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('admin')->user();

        if (!$user || !$user->isAdmin()) {

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
