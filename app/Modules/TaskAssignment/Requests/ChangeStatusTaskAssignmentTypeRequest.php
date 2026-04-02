<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusTaskAssignmentTypeRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['status' => 'required|in:active,inactive'];
    }

    public function bodyParameters(): array { return []; }
}
