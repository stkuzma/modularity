<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use App\Modules\Auth\Data\Credentials;
use Illuminate\Foundation\Http\FormRequest;

final class SignInRequest extends FormRequest
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
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
        ];
    }

    public function toCredentials(): Credentials
    {
        return new Credentials(
            email: mb_strtolower(trim($this->string('email')->value())),
            password: $this->string('password')->value(),
            remember: $this->boolean('remember'),
        );
    }
}
