<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Users\Presenters\UserPresenter;
use App\Modules\Users\Processors\ShowUser;
use Illuminate\Http\JsonResponse;

final class ShowUserController extends ApiController
{
    public function __construct(
        private readonly ShowUser $processor,
        private readonly UserPresenter $presenter,
    ) {}

    public function __invoke(int $user): JsonResponse
    {
        return $this->ok($this->presenter->present($this->processor->process($user)));
    }
}
