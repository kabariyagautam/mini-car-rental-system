<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    // Your levels, dontReport, dontFlash

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            Log::error($e);
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($this->shouldReturnJson($request)) {
                $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;
                $message = $e->getMessage() ?: ($status === 500 ? 'Server Error' : 'Error');

                return response()->json([
                    'error' => true,
                    'message' => $message,
                    'status' => $status,
                ], $status);
            }
        });
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($this->shouldReturnJson($request)) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }

    protected function shouldReturnJson($request): bool
    {
        return $request instanceof Request
            ? $request->expectsJson() || $request->wantsJson() || $request->is('api/*')
            : true;
    }
}
