<?php

namespace App\Modules\TaskAssignment\Requests;

use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use Illuminate\Foundation\Http\FormRequest;

class ChangeStatusTaskAssignmentItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['status' => ['required', TaskProgressStatusEnum::rule()]];
    }

    public function bodyParameters(): array { return []; }
}
