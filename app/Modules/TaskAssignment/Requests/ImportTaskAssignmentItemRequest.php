<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ImportTaskAssignmentItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return ['file' => 'required|file|mimes:xlsx,xls,csv|max:10240'];
    }

    public function bodyParameters(): array { return []; }
}
