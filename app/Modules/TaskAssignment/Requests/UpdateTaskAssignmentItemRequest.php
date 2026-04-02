<?php

namespace App\Modules\TaskAssignment\Requests;

use App\Modules\TaskAssignment\Enums\TaskDeadlineTypeEnum;
use App\Modules\TaskAssignment\Enums\TaskPriorityEnum;
use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use App\Modules\TaskAssignment\Enums\TaskAssignmentRoleEnum;
use App\Modules\TaskAssignment\Enums\TaskAssignmentUserRoleEnum;
use App\Modules\TaskAssignment\Enums\TaskAssignmentUserStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignmentItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'task_assignment_item_type_id' => 'nullable|integer|exists:task_assignment_item_types,id',
            'deadline_type' => ['sometimes', TaskDeadlineTypeEnum::rule()],
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'processing_status' => ['nullable', TaskProgressStatusEnum::rule()],
            'completion_percent' => 'nullable|integer|min:0|max:100',
            'priority' => ['nullable', TaskPriorityEnum::rule()],
            'department_ids' => 'nullable|array',
            'department_ids.*.department_id' => 'required|integer|exists:task_assignment_departments,id',
            'department_ids.*.role' => ['nullable', TaskAssignmentRoleEnum::rule()],
            'user_assignments' => 'nullable|array',
            'user_assignments.*.user_id' => 'required|integer|exists:users,id',
            'user_assignments.*.department_id' => 'required|integer|exists:task_assignment_departments,id',
            'user_assignments.*.assignment_role' => ['nullable', TaskAssignmentUserRoleEnum::rule()],
            'user_assignments.*.assignment_status' => ['nullable', TaskAssignmentUserStatusEnum::rule()],
            'user_assignments.*.note' => 'nullable|string',
        ];
    }

    public function bodyParameters(): array { return []; }
}
