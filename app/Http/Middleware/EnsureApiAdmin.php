<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Admin gate for the mobile API. The web `Allow` middleware redirects to the
 * login page, which is useless to an app expecting JSON.
 */
class EnsureApiAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'status'  => false,
                'message' => 'This area is for administrators only.',
            ], 403);
        }

        return $next($request);
    }
}
