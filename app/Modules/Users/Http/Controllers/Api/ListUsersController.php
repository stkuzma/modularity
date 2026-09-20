<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Api;

use App\Core\Http\ApiController;
use App\Modules\Users\Http\Requests\ListUsersRequest;
use App\Modules\Users\Presenters\UserPresenter;
use App\Modules\Users\Processors\ListUsers;
use Illuminate\Http\JsonResponse;

final class ListUsersController extends ApiController
{
    public function __construct(
        private readonly ListUsers $processor,
        private readonly UserPresenter $presenter,
    ) {}

    public function __invoke(ListUsersRequest $request): JsonResponse
    {
        $page = $this->processor->process($request->toFilter());

        return $this->ok(
            $this->presenter->collection($page->items()),
            [
                'page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        );
    }
}
