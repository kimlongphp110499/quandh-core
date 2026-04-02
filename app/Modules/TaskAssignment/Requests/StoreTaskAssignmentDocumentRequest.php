<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAssignmentDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'summary' => 'nullable|string',
            'issue_date' => 'nullable|date',
            'task_assignment_type_id' => 'nullable|integer|exists:task_assignment_types,id',
            'status' => 'nullable|in:draft,issued',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Tên văn bản giao việc không được để trống.',
            'task_assignment_type_id.exists' => 'Loại văn bản không tồn tại.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
