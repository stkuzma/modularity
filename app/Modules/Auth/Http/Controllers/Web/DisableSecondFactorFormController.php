<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Core\Auth\ActorId;
use App\Modules\Auth\Data\MfaChallenge;
use App\Modules\Auth\Http\Requests\SecondFactorRequest;
use App\Modules\Auth\Processors\DisableMfa;
use Illuminate\Http\RedirectResponse;

final class DisableSecondFactorFormController
{
    public function __construct(private readonly DisableMfa $processor) {}

    public function __invoke(SecondFactorRequest $request): RedirectResponse
    {
        $this->processor->process(new MfaChallenge(
            userId: ActorId::required($request->user()),
            code: $request->code(),
        ));

        return redirect()->route('security.show')->with('status', 'Two-factor authentication is off.');
    }
}
