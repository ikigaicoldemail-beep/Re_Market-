<?php

namespace App\Http\Middleware;

use App\Services\ActivityLogService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogApiActivity
{
    public function __construct(private readonly ActivityLogService $activityLogService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $response;
        }

        $user = $request->user();

        if (! $user) {
            return $response;
        }

        $this->activityLogService->log([
            'user_id' => $user->id,
            'event' => 'api_request',
            'method' => $request->method(),
            'path' => '/'.$request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'response_status' => $response->getStatusCode(),
            'metadata' => [
                'route_name' => optional($request->route())->getName(),
            ],
        ]);

        return $response;
    }
}
