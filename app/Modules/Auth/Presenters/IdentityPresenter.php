<?php

declare(strict_types=1);

namespace App\Modules\Auth\Presenters;

use App\Core\Pipeline\Presenter;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * @implements Presenter<Authenticatable>
 */
final readonly class IdentityPresenter implements Presenter
{
    public function present(mixed $subject): array
    {
        return [
            'id' => $subject->getAuthIdentifier(),
            'name' => $this->attribute($subject, 'name'),
            'email' => $this->attribute($subject, 'email'),
        ];
    }

    private function attribute(Authenticatable $subject, string $key): ?string
    {
        if (! $subject instanceof Model) {
            return null;
        }

        $value = $subject->getAttribute($key);

        return is_string($value) ? $value : null;
    }
}
