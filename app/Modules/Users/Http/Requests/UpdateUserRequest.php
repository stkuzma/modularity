<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Modules\Users\Data\UpdateUserData;
use App\Modules\Users\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

final class UpdateUserRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255'],
            'password' => ['sometimes', 'string', Password::min(8)],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
        ];
    }

    public function toData(): UpdateUserData
    {
        return new UpdateUserData(
            name: $this->has('name') ? trim($this->string('name')->value()) : null,
            email: $this->has('email') ? mb_strtolower(trim($this->string('email')->value())) : null,
            password: $this->has('password') ? $this->string('password')->value() : null,
            status: $this->enum('status', UserStatus::class),
        );
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('password') && trim($this->string('password')->value()) === '') {
            $this->request->remove('password');
        }
    }
}
