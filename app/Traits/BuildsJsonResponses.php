<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait BuildsJsonResponses
{
    protected function jsonSuccess(array $data = [], int $status = 200, array $extras = []): JsonResponse
    {
        return response()->json(array_merge(['success' => true, 'data' => $data], $extras), $status);
    }

    protected function jsonError(string $code, string $message, int $status = 400, array $extras = []): JsonResponse
    {
        return response()->json(array_merge([
            'success' => false,
            'error' => $code,
            'message' => $message,
        ], $extras), $status);
    }
}
