<?php

namespace App\Modules\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:255',
            'guard_name'      => 'nullable|string|max:255',
            'organization_id' => 'nullable|exists:organizations,id',
            'permission_ids'  => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'Tên vai trò không được để trống.',
            'organization_id.exists' => 'Organization không tồn tại.',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
