<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAssignmentItemTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Tên loại công việc không được để trống.'];
    }

    public function bodyParameters(): array { return []; }
}
