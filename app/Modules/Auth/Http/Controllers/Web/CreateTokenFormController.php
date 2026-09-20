<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Core\Auth\ActorId;
use App\Modules\Auth\Data\TokenRequest;
use App\Modules\Auth\Http\Requests\IssueTokenRequest;
use App\Modules\Auth\Processors\IssueToken;
use Illuminate\Http\RedirectResponse;

final class CreateTokenFormController
{
    public function __construct(private readonly IssueToken $processor) {}

    public function __invoke(IssueTokenRequest $request): RedirectResponse
    {
        $issued = $this->processor->process(new TokenRequest(
            userId: ActorId::required($request->user()),
            name: $request->tokenName(),
            expiresInDays: $request->expiresInDays(),
        ));

        return redirect()->route('security.show')->with('issued_token', $issued->plainText);
    }
}
