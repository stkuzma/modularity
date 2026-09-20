<?php

declare(strict_types=1);

namespace App\Modules\Auth\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class IssueTokenRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:128'],
            'expires_in_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function tokenName(): string
    {
        return trim($this->string('name')->value());
    }

    public function expiresInDays(): ?int
    {
        return $this->has('expires_in_days') ? $this->integer('expires_in_days') : null;
    }
}
