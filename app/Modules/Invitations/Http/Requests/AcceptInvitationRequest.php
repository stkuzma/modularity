<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Requests;

use App\Modules\Invitations\Data\AcceptData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class AcceptInvitationRequest extends FormRequest
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
            'token' => ['required', 'string', 'max:128'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', Password::min(8)],
        ];
    }

    public function toData(): AcceptData
    {
        return new AcceptData(
            token: trim($this->string('token')->value()),
            name: trim($this->string('name')->value()),
            password: $this->string('password')->value(),
        );
    }
}
