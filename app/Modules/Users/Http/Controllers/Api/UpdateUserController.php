<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Presenters\UserPresenter;
use App\Modules\Users\Processors\UpdateUser;
use Illuminate\Http\JsonResponse;

final class UpdateUserController extends ApiController
{
    public function __construct(
        private readonly UpdateUser $processor,
        private readonly UserPresenter $presenter,
    ) {}

    public function __invoke(UpdateUserRequest $request, int $user): JsonResponse
    {
        $updated = $this->processor->process([
            'id' => $user,
            'data' => $request->toData(),
        ]);

        return $this->ok($this->presenter->present($updated));
    }
}
