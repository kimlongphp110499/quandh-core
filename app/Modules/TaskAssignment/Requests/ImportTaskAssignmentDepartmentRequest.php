<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportTaskAssignmentDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Vui lòng chọn file import.',
            'file.mimes' => 'File phải có định dạng xlsx, xls hoặc csv.',
        ];
    }

    public function bodyParameters(): array { return []; }
}
