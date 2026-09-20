<?php

declare(strict_types=1);

namespace App\Core\Http\Responses;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<string, mixed>  $meta
     */
    public static function success(array $data, array $meta = [], int $status = 200): JsonResponse
    {
        /** @var array<string, mixed> $payload */
        $payload = ['data' => $data];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return new JsonResponse($payload, $status);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    public static function failure(
        string $code,
        string $message,
        array $details = [],
        int $status = 400,
    ): JsonResponse {
        /** @var array<string, mixed> $error */
        $error = ['code' => $code, 'message' => $message];

        if ($details !== []) {
            $error['details'] = $details;
        }

        return new JsonResponse(['error' => $error], $status);
    }
}
