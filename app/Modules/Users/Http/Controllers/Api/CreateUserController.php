<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Users\Http\Requests\CreateUserRequest;
use App\Modules\Users\Presenters\UserPresenter;
use App\Modules\Users\Processors\CreateUser;
use Illuminate\Http\JsonResponse;

final class CreateUserController extends ApiController
{
    public function __construct(
        private readonly CreateUser $processor,
        private readonly UserPresenter $presenter,
    ) {}

    public function __invoke(CreateUserRequest $request): JsonResponse
    {
        $user = $this->processor->process($request->toData());

        return $this->created($this->presenter->present($user));
    }
}
