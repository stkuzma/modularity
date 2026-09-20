<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Core\Auth\ActorId;
use App\Modules\Auth\Processors\RevokeToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RevokeTokenFormController
{
    public function __construct(private readonly RevokeToken $processor) {}

    public function __invoke(Request $request, int $token): RedirectResponse
    {
        $this->processor->process([
            'userId' => ActorId::required($request->user()),
            'tokenId' => $token,
        ]);

        return redirect()->route('security.show')->with('status', 'Token revoked.');
    }
}
