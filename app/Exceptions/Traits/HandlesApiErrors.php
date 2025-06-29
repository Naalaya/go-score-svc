<?php

namespace App\Exceptions\Traits;

use Illuminate\Http\JsonResponse;

trait HandlesApiErrors
{
    protected function errorResponse(string $message, string $errorCode = 'ERROR', int $statusCode = 400): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => $errorCode,
            'message' => $message,
        ], $statusCode);
    }

    protected function validationErrorResponse(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => 'VALIDATION_ERROR',
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 422);
    }
}
