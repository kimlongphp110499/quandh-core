<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStatusTaskAssignmentDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:task_assignment_departments,id',
            'status' => 'required|in:active,inactive',
        ];
    }

    public function messages(): array
    {
        return [
            'ids.required' => 'Bạn chưa chọn phòng ban nào.',
            'status.required' => 'Trạng thái không được để trống.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
