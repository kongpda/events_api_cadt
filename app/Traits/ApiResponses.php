<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function ok(string $message, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], $statusCode);
    }

    protected function success(string $message, int $statusCode = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode,
        ], $statusCode);
    }

    protected function error(string $message, int $statusCode = 500): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'status' => $statusCode,
        ], $statusCode);
    }
}
