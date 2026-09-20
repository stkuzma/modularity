<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Controllers\Web;

use App\Modules\Users\Processors\DeleteUser;
use Illuminate\Http\RedirectResponse;

final class DeleteUserFormController
{
    public function __construct(private readonly DeleteUser $processor) {}

    public function __invoke(int $user): RedirectResponse
    {
        $this->processor->process($user);

        return redirect()->route('users.index')->with('status', 'The user was deleted.');
    }
}
