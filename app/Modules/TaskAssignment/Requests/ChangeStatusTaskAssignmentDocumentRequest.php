<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusTaskAssignmentDocumentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['status' => 'required|in:draft,issued'];
    }

    public function messages(): array
    {
        return ['status.in' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: draft, issued.'];
    }

    public function bodyParameters(): array { return []; }
}
