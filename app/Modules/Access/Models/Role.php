<?php

declare(strict_types=1);

namespace App\Modules\Access\Models;

use App\Modules\Access\Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property bool $is_system
 * @property Collection<int, Permission> $permissions
 */
#[Fillable(['name', 'label', 'is_system'])]
final class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Permission, $this>
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    /**
     * @return RoleFactory
     */
    protected static function newFactory(): Factory
    {
        return RoleFactory::new();
    }
}
