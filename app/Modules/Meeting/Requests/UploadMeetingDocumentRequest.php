<?php

namespace App\Modules\Meeting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMeetingDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'documents' => 'required|array|min:1|max:20',
            'documents.*' => 'file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,png,jpg,jpeg|max:51200', // tối đa 50MB
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'documents.required' => 'Phải chọn ít nhất 1 tài liệu.',
            'documents.*.file' => 'File không hợp lệ.',
            'documents.*.mimes' => 'Chỉ chấp nhận file: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, png, jpg, jpeg.',
            'documents.*.max' => 'Mỗi tài liệu tối đa 50MB.',
        ];
    }
}
