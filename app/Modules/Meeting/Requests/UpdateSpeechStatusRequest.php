<?php

namespace App\Modules\Meeting\Requests;

use App\Modules\Meeting\Enums\MeetingRoleEnum;
use App\Modules\Meeting\Enums\SpeechRequestStatusEnum;
use App\Modules\Meeting\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSpeechStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $meeting = $this->route('meeting');

            $hasRole = Participant::where('meeting_id', $meeting->id)
                ->where('user_id', auth()->id())
                ->whereIn('meeting_role', [MeetingRoleEnum::Chair->value, MeetingRoleEnum::Secretary->value])
                ->exists();

            if (! $hasRole) {
                $validator->errors()->add('meeting_role', 'Chỉ chủ trì hoặc thư ký mới được duyệt đăng ký phát biểu.');
            }
        });
    }

    public function rules(): array
    {
        return [
            'status' => ['required', SpeechRequestStatusEnum::rule()],
            'rejection_reason' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Trạng thái không được để trống.',
            'status.in' => 'Trạng thái không hợp lệ. Chấp nhận: pending, approved, rejected.',
        ];
    }
}
