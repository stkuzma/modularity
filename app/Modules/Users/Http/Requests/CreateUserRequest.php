<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Modules\Users\Data\CreateUserData;
use App\Modules\Users\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', Password::min(8)],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
        ];
    }

    public function toData(): CreateUserData
    {
        return new CreateUserData(
            name: trim($this->string('name')->value()),
            email: mb_strtolower(trim($this->string('email')->value())),
            password: $this->string('password')->value(),
            status: $this->enum('status', UserStatus::class) ?? UserStatus::Active,
        );
    }
}
