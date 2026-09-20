<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Web;

use App\Modules\Users\Http\Requests\UpdateUserRequest;
use App\Modules\Users\Processors\UpdateUser;
use Illuminate\Http\RedirectResponse;

final class UpdateUserFormController
{
    public function __construct(private readonly UpdateUser $processor) {}

    public function __invoke(UpdateUserRequest $request, int $user): RedirectResponse
    {
        $updated = $this->processor->process(['id' => $user, 'data' => $request->toData()]);

        return redirect()->route('users.index')->with('status', "{$updated->name} was updated.");
    }
}
