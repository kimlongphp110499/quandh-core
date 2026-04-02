<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyTaskAssignmentDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:task_assignment_departments,id',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Bạn chưa chọn phòng ban nào.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
