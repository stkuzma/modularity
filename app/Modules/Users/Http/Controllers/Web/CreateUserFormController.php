<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Web;

use App\Modules\Users\Http\Requests\CreateUserRequest;
use App\Modules\Users\Processors\CreateUser;
use Illuminate\Http\RedirectResponse;

final class CreateUserFormController
{
    public function __construct(private readonly CreateUser $processor) {}

    public function __invoke(CreateUserRequest $request): RedirectResponse
    {
        $user = $this->processor->process($request->toData());

        return redirect()->route('users.index')->with('status', "{$user->name} was created.");
    }
}
