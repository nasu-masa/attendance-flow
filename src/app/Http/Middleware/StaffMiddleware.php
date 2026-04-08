<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (!$user || !$user->isStaff()) {

            logger()->warning('Non-staff user attempted to access staff route', [
                'user_id' => $user?->id,
                'route'   => $request->path(),
                'ip'      => $request->ip(),
            ]);

            abort(403);
        }

        return $next($request);
    }
}
