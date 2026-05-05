<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->status !== 'active') {
            abort(Response::HTTP_FORBIDDEN, 'This account is not active.');
        }

        return $next($request);
    }
}
