<?php

namespace App\Modules\Meeting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agenda_id' => 'nullable|integer|exists:m_agendas,id',
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề kết luận không được để trống.',
            'content.required' => 'Nội dung kết luận không được để trống.',
        ];
    }
}
