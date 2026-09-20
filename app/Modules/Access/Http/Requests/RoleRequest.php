<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Requests;

use App\Modules\Access\Data\RoleData;
use Illuminate\Foundation\Http\FormRequest;

final class RoleRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:64', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/'],
            'label' => ['required', 'string', 'max:255'],
            'permissions' => ['sometimes', 'array'],
            'permissions.*' => ['string', 'max:128'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The role name must be a lowercase slug, for example "support-lead".',
        ];
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        $names = array_filter($this->array('permissions'), 'is_string');

        return array_values(array_unique($names));
    }

    public function toData(): RoleData
    {
        return new RoleData(
            name: mb_strtolower(trim($this->string('name')->value())),
            label: trim($this->string('label')->value()),
        );
    }
}
