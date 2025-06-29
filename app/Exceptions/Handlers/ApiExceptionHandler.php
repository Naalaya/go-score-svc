<?php

namespace App\Exceptions\Handlers;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApiExceptionHandler extends ExceptionHandler
{
    public function render($request, Throwable $exception): JsonResponse
    {
        // Handle validation exceptions
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'error' => 'VALIDATION_ERROR',
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], 422);
        }

        // Handle custom API exceptions
        if (method_exists($exception, 'render') && is_callable([$exception, 'render'])) {
            return call_user_func([$exception, 'render']);
        }

        // Handle general exceptions
        return response()->json([
            'success' => false,
            'error' => 'INTERNAL_ERROR',
            'message' => app()->environment('production')
                ? 'An error occurred while processing your request.'
                : $exception->getMessage(),
        ], 500);
    }
}
