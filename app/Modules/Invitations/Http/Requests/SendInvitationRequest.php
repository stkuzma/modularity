<?php

declare(strict_types=1);

namespace App\Modules\Invitations\Http\Requests;

use App\Core\Auth\ActorId;
use App\Modules\Invitations\Data\InviteData;
use Illuminate\Foundation\Http\FormRequest;

final class SendInvitationRequest extends FormRequest
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
            'valid_for_days' => ['sometimes', 'integer', 'min:1', 'max:30'],
        ];
    }

    public function toData(): InviteData
    {
        return new InviteData(
            email: mb_strtolower(trim($this->string('email')->value())),
            invitedBy: ActorId::optional($this->user()),
            validForDays: $this->integer('valid_for_days', 7),
        );
    }
}
