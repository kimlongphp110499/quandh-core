<?php

namespace App\Modules\TaskAssignment\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatsPeriodTaskAssignmentDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Nhóm theo: month (tháng), quarter (quý), year (năm)
            'group_by'                   => 'required|in:month,quarter,year',
            'year'                       => 'nullable|integer|min:2000|max:2100',
            'task_assignment_type_id'    => 'nullable|integer|exists:task_assignment_types,id',
            'status'                     => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'group_by.required' => 'Vui lòng chọn kiểu nhóm thống kê (month, quarter, year).',
            'group_by.in'       => 'Kiểu nhóm không hợp lệ. Chỉ chấp nhận: month, quarter, year.',
            'year.integer'      => 'Năm phải là số nguyên.',
            'year.min'          => 'Năm không được nhỏ hơn 2000.',
            'year.max'          => 'Năm không được lớn hơn 2100.',
        ];
    }
}
