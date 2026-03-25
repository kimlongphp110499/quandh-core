<?php

namespace App\Modules\Meeting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConclusionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agenda_id' => 'nullable|integer|exists:m_agendas,id',
            'title' => 'required|string|max:500',
            'content' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề kết luận không được để trống.',
            'content.required' => 'Nội dung kết luận không được để trống.',
            'agenda_id.exists' => 'Mục chương trình họp không tồn tại.',
        ];
    }
}
