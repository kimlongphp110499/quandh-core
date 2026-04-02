<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAssignmentDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:task_assignment_departments,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Mã phòng ban không được để trống.',
            'code.unique' => 'Mã phòng ban đã tồn tại.',
            'name.required' => 'Tên phòng ban không được để trống.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
