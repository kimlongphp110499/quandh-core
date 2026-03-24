<?php

namespace App\Modules\Meeting\Requests;

use App\Modules\Meeting\Models\Participant;
use Illuminate\Foundation\Http\FormRequest;

class StoreSpeechRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $meeting = $this->route('meeting');

            $isParticipant = Participant::where('meeting_id', $meeting->id)
                ->where('user_id', auth()->id())
                ->exists();

            if (! $isParticipant) {
                $validator->errors()->add('participant', 'Bạn không phải đại biểu của cuộc họp này.');
            }
        });
    }

    public function rules(): array
    {
        return [
            'agenda_id' => 'nullable|integer|exists:m_agendas,id',
            'content' => 'required|string|max:2000',
        ];
    }

    public function messages(): array
    {
        return [
            'agenda_id.exists' => 'Mục họp không tồn tại.',
            'agenda_id.integer' => 'ID mục họp phải là số nguyên.',
            'content.required' => 'Nội dung đăng ký là bắt buộc.',
            'content.max' => 'Nội dung đăng ký tối đa 2000 ký tự.',
        ];
    }
}
