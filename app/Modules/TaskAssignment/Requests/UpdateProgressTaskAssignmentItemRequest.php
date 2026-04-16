<?php

namespace App\Modules\TaskAssignment\Requests;

use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProgressTaskAssignmentItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Trạng thái xử lý mới — không cho phép overdue (hệ thống tự đánh dấu)
            'processing_status' => [
                'sometimes',
                'nullable',
                Rule::in([
                    TaskProgressStatusEnum::Todo->value,
                    TaskProgressStatusEnum::InProgress->value,
                    TaskProgressStatusEnum::Done->value,
                    TaskProgressStatusEnum::Paused->value,
                    TaskProgressStatusEnum::Cancelled->value,
                ]),
            ],
            // Phần trăm hoàn thành, 0-100
            'completion_percent' => 'sometimes|nullable|integer|min:0|max:100',
            // Ghi chú tiến độ của người được phân công
            'note' => 'sometimes|nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'processing_status.in' => 'Trạng thái không hợp lệ. Chỉ được chọn: todo, in_progress, done, paused, cancelled.',
            'completion_percent.integer' => 'Phần trăm hoàn thành phải là số nguyên.',
            'completion_percent.min' => 'Phần trăm hoàn thành không được nhỏ hơn 0.',
            'completion_percent.max' => 'Phần trăm hoàn thành không được lớn hơn 100.',
            'note.max' => 'Ghi chú không được vượt quá 1000 ký tự.',
        ];
    }
}
