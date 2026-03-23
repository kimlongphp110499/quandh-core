<?php

namespace App\Modules\Meeting\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgendaRequest extends FormRequest
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
            'order_index' => 'nullable|integer|min:0',
            'duration' => 'nullable|integer|min:1|max:480',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Tiêu đề mục họp không được để trống.',
        ];
    }
}
