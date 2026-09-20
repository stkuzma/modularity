<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Users\Processors\DeleteUser;
use Illuminate\Http\JsonResponse;

final class DeleteUserController extends ApiController
{
    public function __construct(private readonly DeleteUser $processor) {}

    public function __invoke(int $user): JsonResponse
    {
        $this->processor->process($user);

        return $this->noContent();
    }
}
