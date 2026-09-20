<?php

declare(strict_types=1);

namespace App\Modules\Users\Http\Requests;

use App\Modules\Users\Data\UserFilter;
use App\Modules\Users\Enums\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListUsersRequest extends FormRequest
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
            'search' => ['sometimes', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(UserStatus::class)],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function toFilter(): UserFilter
    {
        return new UserFilter(
            search: $this->has('search') ? trim($this->string('search')->value()) : null,
            status: $this->enum('status', UserStatus::class),
            perPage: $this->integer('per_page', 25),
            page: $this->integer('page', 1),
        );
    }
}
