<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rate limiting middleware based on user's team
 *
 * This middleware applies rate limiting per team to prevent abuse
 * and ensure fair resource usage across teams.
 */
class RateLimitByTeam
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 60, int $decaySeconds = 60): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Get the team ID (or user ID if no team)
        $teamId = $user->currentTeam?->id ?? $user->id;
        $key = 'rate_limit:team:'.$teamId.':'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return response()->json([
                'message' => 'Too many requests. Please try again in '.$seconds.' seconds.',
                'retry_after' => $seconds,
            ], 429)
                ->withHeaders([
                    'Retry-After' => $seconds,
                    'X-RateLimit-Limit' => $maxAttempts,
                    'X-RateLimit-Remaining' => 0,
                ]);
        }

        RateLimiter::hit($key, $decaySeconds);

        $response = $next($request);

        $remaining = $maxAttempts - RateLimiter::attempts($key);

        return $response->withHeaders([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $remaining),
        ]);
    }
}
