<?php

declare(strict_types=1);

namespace App\Modules\Access\Database\Factories;

use App\Modules\Access\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
final class RoleFactory extends Factory
{
    protected $model = Role::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->unique()->jobTitle();

        return [
            'name' => Str::slug($label),
            'label' => $label,
            'is_system' => false,
        ];
    }

    public function system(): self
    {
        return $this->state(fn (): array => ['is_system' => true]);
    }
}
