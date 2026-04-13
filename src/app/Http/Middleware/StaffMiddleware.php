<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class StaffMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->role !== User::ROLE_STAFF) {

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
