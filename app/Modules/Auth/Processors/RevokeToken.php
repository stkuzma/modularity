<?php

declare(strict_types=1);

namespace App\Modules\Auth\Processors;

use App\Core\Audit\AuditTrail;
use App\Core\Exceptions\NotFoundException;
use App\Core\Pipeline\Processor;
use App\Modules\Auth\Contracts\TokenRepository;

/**
 * @phpstan-type RevokeInput array{userId: int, tokenId: int}
 *
 * @implements Processor<RevokeInput, null>
 */
final readonly class RevokeToken implements Processor
{
    public function __construct(
        private TokenRepository $tokens,
        private AuditTrail $audit,
    ) {}

    public function process(mixed $input): null
    {
        // 404 rather than 403: do not confirm someone else's token id exists.
        $token = $this->tokens->findForUser($input['userId'], $input['tokenId'])
            ?? throw NotFoundException::resource('auth_tokens', $input['tokenId']);

        $this->tokens->delete($token);

        $this->audit->record('auth.token.revoked', 'auth_token', $input['tokenId'], [
            'name' => $token->name,
        ]);

        return null;
    }
}
