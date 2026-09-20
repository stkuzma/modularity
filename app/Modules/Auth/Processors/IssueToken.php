<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Pipeline\Processor;
use App\Modules\Auth\Contracts\TokenRepository;
use App\Modules\Auth\Data\IssuedToken;
use App\Modules\Auth\Data\TokenRequest;
use App\Modules\Auth\Services\TokenMint;
use Illuminate\Support\Carbon;

/**
 * @implements Processor<TokenRequest, IssuedToken>
 */
final readonly class IssueToken implements Processor
{
    public function __construct(
        private TokenRepository $tokens,
        private TokenMint $mint,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): IssuedToken
    {
        $plainText = $this->mint->generate();

        $token = $this->tokens->create(
            userId: $input->userId,
            name: $input->name,
            tokenHash: $this->mint->hash($plainText),
            expiresAt: $input->expiresInDays === null
                ? null
                : Carbon::now()->addDays($input->expiresInDays),
        );

        $this->audit->record('auth.token.issued', 'auth_token', $token->id, [
            'name' => $token->name,
            'expires_at' => $token->expires_at?->toAtomString(),
        ]);

        return new IssuedToken($token, $plainText);
    }
}
