<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Middleware;

use App\Core\Auth\ActorId;
use App\Core\Exceptions\ForbiddenException;
use App\Modules\Auth\Contracts\MfaRepository;
use App\Modules\Auth\Support\SecondFactorSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class RequireSecondFactor
{
    public function __construct(private MfaRepository $mfa) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || $request->bearerToken() !== null) {
            return $next($request);
        }

        $secret = $this->mfa->findFor(ActorId::required($user));

        if ($secret?->isConfirmed() === true && ! SecondFactorSession::isVerified($request)) {
            throw new ForbiddenException(
                message: 'This session has not completed two-factor authentication.',
                domainCode: 'auth.second_factor_required',
            );
        }

        return $next($request);
    }
}
