<?php

namespace App\Modules\TaskAssignment\Requests;

use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStatusTaskAssignmentItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:task_assignment_items,id',
            'status' => ['required', TaskProgressStatusEnum::rule()],
        ];
    }

    public function bodyParameters(): array { return []; }
}
