<?php

declare(strict_types=1);

namespace App\Core\Http;

use App\Core\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;

abstract class ApiController extends Controller
{
    /**
     * @param  array<array-key, mixed>  $data
     * @param  array<string, mixed>  $meta
     */
    protected function ok(array $data, array $meta = []): JsonResponse
    {
        return ApiResponse::success($data, $meta);
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    protected function created(array $data): JsonResponse
    {
        return ApiResponse::success($data, [], 201);
    }

    protected function noContent(): JsonResponse
    {
        return new JsonResponse(null, 204);
    }
}
