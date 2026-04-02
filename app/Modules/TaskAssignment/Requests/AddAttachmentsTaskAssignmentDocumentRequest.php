<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddAttachmentsTaskAssignmentDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'files' => 'required|array|min:1',
            'files.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:20480',
        ];
    }

    public function messages(): array
    {
        return [
            'files.required' => 'Vui lòng chọn ít nhất một tệp.',
            'files.*.mimes' => 'Tệp phải có định dạng pdf, doc, docx, xls, xlsx, ppt, pptx.',
            'files.*.max' => 'Mỗi tệp không được vượt quá 20MB.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
