<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Web;

use App\Core\Auth\ActorId;
use App\Modules\Auth\Processors\BeginMfaEnrolment;
use App\Modules\Auth\Services\QrCode;
use Illuminate\Config\Repository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EnrolSecondFactorFormController
{
    public function __construct(
        private readonly BeginMfaEnrolment $processor,
        private readonly Repository $config,
        private readonly QrCode $qr,
    ) {}

    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        $email = $user?->getAttribute('email');

        $enrolment = $this->processor->process([
            'userId' => ActorId::required($user),
            'account' => is_string($email) ? $email : 'account',
            'issuer' => $this->config->string('app.name', 'Modularity'),
        ]);

        // Flashed, not stored: readable on the next render and never again.
        return back()->with([
            'mfa_secret' => $enrolment->secret,
            'mfa_qr' => $this->qr->svg($enrolment->provisioningUri),
            'mfa_uri' => $enrolment->provisioningUri,
            'mfa_recovery_codes' => $enrolment->recoveryCodes,
        ]);
    }
}
