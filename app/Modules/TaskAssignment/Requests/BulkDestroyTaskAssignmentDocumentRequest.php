<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDestroyTaskAssignmentDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:task_assignment_documents,id',
        ];
    }

    public function bodyParameters(): array { return []; }
}
