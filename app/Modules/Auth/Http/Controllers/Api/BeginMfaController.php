<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Controllers\Api;

use App\Core\Auth\ActorId;
use App\Core\Http\ApiController;
use App\Modules\Auth\Processors\BeginMfaEnrolment;
use Illuminate\Config\Repository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class BeginMfaController extends ApiController
{
    public function __construct(
        private readonly BeginMfaEnrolment $processor,
        private readonly Repository $config,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        $enrolment = $this->processor->process([
            'userId' => ActorId::required($user),
            'account' => (string) ($request->string('account')->value() ?: 'account'),
            'issuer' => $this->issuer(),
        ]);

        return $this->created([
            'secret' => $enrolment->secret,
            'provisioning_uri' => $enrolment->provisioningUri,
            // Shown once. There is no endpoint that returns them again.
            'recovery_codes' => $enrolment->recoveryCodes,
        ]);
    }

    private function issuer(): string
    {
        return $this->config->string('app.name', 'Modularity');
    }
}
