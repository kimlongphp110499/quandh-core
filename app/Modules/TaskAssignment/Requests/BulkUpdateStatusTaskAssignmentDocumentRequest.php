<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateStatusTaskAssignmentDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:task_assignment_documents,id',
            'status' => 'required|in:draft,issued',
        ];
    }

    public function bodyParameters(): array { return []; }
}
