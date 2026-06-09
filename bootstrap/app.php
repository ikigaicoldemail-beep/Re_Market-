<?php

use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureEmailIsVerifiedForMarketplace;
use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\LogApiActivity;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        $middleware->alias([
            'activity.log' => LogApiActivity::class,
            'account.active' => EnsureAccountIsActive::class,
            'email.verified.marketplace' => EnsureEmailIsVerifiedForMarketplace::class,
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(function (Request $request, \Throwable $throwable) {
            return $request->is('api/*') || $request->expectsJson();
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            return response()->json([
                'message' => $exception->getMessage() ?: 'This action is unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            return response()->json([
                'message' => 'Resource not found.',
            ], Response::HTTP_NOT_FOUND);
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            return response()->json([
                'message' => 'Endpoint not found.',
            ], Response::HTTP_NOT_FOUND);
        });
    })->create();
