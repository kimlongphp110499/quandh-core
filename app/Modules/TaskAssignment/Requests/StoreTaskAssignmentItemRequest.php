<?php

namespace App\Modules\TaskAssignment\Requests;

use App\Modules\TaskAssignment\Enums\TaskDeadlineTypeEnum;
use App\Modules\TaskAssignment\Enums\TaskPriorityEnum;
use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use App\Modules\TaskAssignment\Enums\TaskAssignmentRoleEnum;
use App\Modules\TaskAssignment\Enums\TaskAssignmentUserRoleEnum;
use App\Modules\TaskAssignment\Enums\TaskAssignmentUserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreTaskAssignmentItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'task_assignment_document_id' => 'required|integer|exists:task_assignment_documents,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_assignment_item_type_id' => 'nullable|integer|exists:task_assignment_item_types,id',
            'deadline_type' => ['required', TaskDeadlineTypeEnum::rule()],
            'start_at' => 'nullable|date',
            'end_at' => 'required_if:deadline_type,has_deadline|nullable|date|after_or_equal:start_at',
            'processing_status' => ['nullable', TaskProgressStatusEnum::rule()],
            'completion_percent' => 'nullable|integer|min:0|max:100',
            'priority' => ['nullable', TaskPriorityEnum::rule()],
            // Phòng ban thực hiện
            'department_ids' => 'nullable|array',
            'department_ids.*.department_id' => 'required|integer|exists:task_assignment_departments,id',
            'department_ids.*.role' => ['nullable', TaskAssignmentRoleEnum::rule()],
            // Người thực hiện
            'user_assignments' => 'nullable|array',
            'user_assignments.*.user_id' => 'required|integer|exists:users,id',
            'user_assignments.*.department_id' => 'required|integer|exists:task_assignment_departments,id',
            'user_assignments.*.assignment_role' => ['nullable', TaskAssignmentUserRoleEnum::rule()],
            'user_assignments.*.assignment_status' => ['nullable', TaskAssignmentUserStatusEnum::rule()],
            'user_assignments.*.note' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'task_assignment_document_id.required' => 'Văn bản giao việc không được để trống.',
            'task_assignment_document_id.exists' => 'Văn bản giao việc không tồn tại.',
            'name.required' => 'Tên công việc không được để trống.',
            'deadline_type.required' => 'Loại thời hạn không được để trống.',
            'end_at.required_if' => 'Ngày kết thúc bắt buộc khi công việc có thời hạn.',
            'end_at.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
