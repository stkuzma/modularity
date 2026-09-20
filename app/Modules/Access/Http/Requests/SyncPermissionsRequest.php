<?php

declare(strict_types=1);

namespace App\Modules\Access\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SyncPermissionsRequest extends FormRequest
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
            'permissions' => ['present', 'array'],
            'permissions.*' => ['string', 'max:128'],
        ];
    }

    /**
     * @return list<string>
     */
    public function permissionNames(): array
    {
        /** @var list<string> $names */
        $names = array_values(array_unique($this->array('permissions')));

        return $names;
    }
}
