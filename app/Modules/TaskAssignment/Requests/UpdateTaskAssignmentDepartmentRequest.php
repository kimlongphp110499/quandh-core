<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignmentDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('taskAssignmentDepartment')?->id ?? $this->route('id');

        return [
            'code' => "sometimes|string|max:50|unique:task_assignment_departments,code,{$id}",
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'sort_order' => 'nullable|integer|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => 'Mã phòng ban đã tồn tại.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
