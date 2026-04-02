<?php

namespace App\Modules\TaskAssignment\Exports;

use App\Modules\TaskAssignment\Models\TaskAssignmentDepartment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaskAssignmentDepartmentsExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $filters = []) {}

    public function collection()
    {
        return TaskAssignmentDepartment::filter($this->filters)->get()->map(fn ($dept) => [
            'id' => $dept->id,
            'code' => $dept->code,
            'name' => $dept->name,
            'description' => $dept->description,
            'status' => $dept->status,
            'sort_order' => $dept->sort_order,
            'created_at' => $dept->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $dept->updated_at?->format('d/m/Y H:i:s'),
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Mã', 'Tên phòng ban', 'Mô tả', 'Trạng thái', 'Thứ tự', 'Ngày tạo', 'Cập nhật'];
    }
}
