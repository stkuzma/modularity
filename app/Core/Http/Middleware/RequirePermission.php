<?php

declare(strict_types=1);

namespace App\Core\Http\Middleware;

use App\Core\Exceptions\ForbiddenException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $actor = $request->user();

        if ($actor === null || $actor->cannot($permission)) {
            throw new ForbiddenException(
                message: 'You do not have permission to perform this action.',
                domainCode: 'core.forbidden',
                details: ['permission' => $permission],
            );
        }

        return $next($request);
    }
}
