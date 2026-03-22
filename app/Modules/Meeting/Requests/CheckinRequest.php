<?php

namespace App\Modules\Meeting\Requests;

use App\Modules\Meeting\Enums\AttendanceStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class CheckinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_status' => ['required', AttendanceStatusEnum::rule()],
            'absence_reason' => 'required_if:attendance_status,absent|nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_status.required' => 'Trạng thái điểm danh không được để trống.',
            'attendance_status.in' => 'Trạng thái điểm danh không hợp lệ.',
            'absence_reason.required_if' => 'Lý do vắng mặt là bắt buộc khi trạng thái là absent.',
        ];
    }
}
