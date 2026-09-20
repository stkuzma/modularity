<?php

declare(strict_types=1);

namespace App\Modules\Auth\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $user_id
 * @property string $secret
 * @property array<int, string> $recovery_codes
 * @property Carbon|null $confirmed_at
 */
#[Fillable(['user_id', 'secret', 'recovery_codes', 'confirmed_at'])]
#[Hidden(['secret', 'recovery_codes'])]
final class MfaSecret extends Model
{
    public $incrementing = false;

    protected $table = 'user_mfa';

    protected $primaryKey = 'user_id';

    public function isConfirmed(): bool
    {
        return $this->confirmed_at !== null;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Encrypted at rest.
            'secret' => 'encrypted',
            'recovery_codes' => 'encrypted:array',
            'confirmed_at' => 'datetime',
        ];
    }
}
