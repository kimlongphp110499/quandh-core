<?php

namespace App\Modules\Meeting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgendaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order_index' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:1|max:480', // tối đa 8 tiếng
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề mục họp không được để trống.',
            'duration.max' => 'Thời lượng tối đa 480 phút (8 tiếng).',
        ];
    }
}
