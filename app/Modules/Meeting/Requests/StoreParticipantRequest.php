<?php

namespace App\Modules\Meeting\Requests;

use App\Modules\Meeting\Enums\MeetingRoleEnum;
use Illuminate\Foundation\Http\FormRequest;

class StoreParticipantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|exists:users,id',
            'meeting_role' => ['required', MeetingRoleEnum::rule()],
            'position' => 'required|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'Phải chọn ít nhất 1 người dùng.',
            'user_ids.*.exists' => 'Người dùng không tồn tại.',
            'meeting_role.required' => 'Vai trò trong cuộc họp không được để trống.',
            'meeting_role.in' => 'Vai trò không hợp lệ. Chấp nhận: chair, secretary, delegate.',
            'position.required' => 'Chức vụ không được để trống.',
            'position.string' => 'Chức vụ phải là chuỗi.',
            'position.max' => 'Chức vụ không được vượt quá 500 ký tự.',
        ];
    }
}
