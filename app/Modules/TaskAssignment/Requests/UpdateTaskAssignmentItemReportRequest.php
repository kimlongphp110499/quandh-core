<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskAssignmentItemReportRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'completed_at' => 'nullable|date',
            'report_document_number' => 'nullable|string|max:100',
            'report_document_excerpt' => 'nullable|string|max:500',
            'report_document_content' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:20480',
            'remove_attachment_ids' => 'nullable|array',
            'remove_attachment_ids.*' => 'integer|exists:task_assignment_item_report_attachments,id',
        ];
    }

    public function bodyParameters(): array { return []; }
}
