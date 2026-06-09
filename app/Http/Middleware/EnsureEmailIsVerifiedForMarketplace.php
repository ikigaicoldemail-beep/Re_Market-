<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedForMarketplace
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->email_verified_at) {
            abort(Response::HTTP_FORBIDDEN, 'Verify your email before using marketplace actions.');
        }

        return $next($request);
    }
}
