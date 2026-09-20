<?php

declare(strict_types=1);

namespace App\Modules\Users\Database\Factories;

use App\Modules\Users\Enums\UserStatus;
use App\Modules\Users\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
final class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function suspended(): self
    {
        return $this->state(fn (): array => ['status' => UserStatus::Suspended]);
    }

    public function unverified(): self
    {
        return $this->state(fn (): array => ['email_verified_at' => null]);
    }
}
