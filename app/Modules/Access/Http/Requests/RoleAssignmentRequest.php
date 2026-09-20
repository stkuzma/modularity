<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RoleAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Gates live on the route; a form request validates shape.
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function userId(): int
    {
        return $this->integer('user_id');
    }
}
