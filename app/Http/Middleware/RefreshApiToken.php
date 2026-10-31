<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Slides a token's expiry forward while it is being used.
 *
 * Sanctum measures expiry from the token's created_at, so without this an
 * employee would be signed out sixty days after first signing in, however
 * often they opened the app. Bumping it keeps an active session alive while
 * still letting a forgotten handset's token lapse.
 */
class RefreshApiToken
{
    /** Only rewrite the row once a day; this runs on every request. */
    private const REFRESH_AFTER_HOURS = 24;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $token = $request->user()?->currentAccessToken();

        if ($token instanceof PersonalAccessToken
            && $token->created_at
            && $token->created_at->lt(now()->subHours(self::REFRESH_AFTER_HOURS))) {
            $token->forceFill(['created_at' => now()])->saveQuietly();
        }

        return $response;
    }
}
