<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Core\Auth\ActorId;
use App\Modules\Auth\Data\MfaChallenge;
use App\Modules\Auth\Http\Requests\SecondFactorRequest;
use App\Modules\Auth\Processors\ConfirmMfaEnrolment;
use App\Modules\Auth\Support\SecondFactorSession;
use Illuminate\Http\RedirectResponse;

final class ConfirmSecondFactorFormController
{
    public function __construct(private readonly ConfirmMfaEnrolment $processor) {}

    public function __invoke(SecondFactorRequest $request): RedirectResponse
    {
        $this->processor->process(new MfaChallenge(
            userId: ActorId::required($request->user()),
            code: $request->code(),
        ));

        SecondFactorSession::markVerified($request);

        return redirect()->route('security.show')->with('status', 'Two-factor authentication is on.');
    }
}
