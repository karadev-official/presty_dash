<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponder
{
    protected function success($data, $code = 200): JsonResponse
    {
        return response()->json(['data' => $data], $code);
    }

    protected function error($message, $code): JsonResponse
    {
        return response()->json([
            'error' => [
                'message' => $message,
                'code' => $code
            ]
        ], $code);
    }
}
