<?php

namespace App\Modules\Meeting\Requests;

use App\Modules\Meeting\Enums\MeetingStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMeetingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date|after_or_equal:start_at',
            'status' => ['nullable', MeetingStatusEnum::rule()],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề cuộc họp không được để trống.',
            'title.max' => 'Tiêu đề không được vượt quá 255 ký tự.',
            'end_at.after_or_equal' => 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.',
            'status.in' => 'Trạng thái cuộc họp không hợp lệ.',
        ];
    }
}
