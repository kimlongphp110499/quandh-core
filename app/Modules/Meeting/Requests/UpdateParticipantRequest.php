<?php

namespace App\Modules\Meeting\Requests;

use App\Modules\Meeting\Enums\MeetingRoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'meeting_role' => ['sometimes', MeetingRoleEnum::rule()],
            'position' => 'sometimes|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'meeting_role.in' => 'Vai trò không hợp lệ.',
            'position.string' => 'Chức vụ phải là chuỗi.',
            'position.max' => 'Chức vụ không được vượt quá 500 ký tự.',
        ];
    }
}
