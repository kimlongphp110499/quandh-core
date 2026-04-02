<?php

namespace App\Modules\TaskAssignment\Exports;

use App\Modules\TaskAssignment\Models\TaskAssignmentItemType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaskAssignmentItemTypesExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $filters = []) {}

    public function collection()
    {
        return TaskAssignmentItemType::filter($this->filters)->get()->map(fn ($type) => [
            'id' => $type->id,
            'name' => $type->name,
            'description' => $type->description,
            'status' => $type->status,
            'created_at' => $type->created_at?->format('d/m/Y H:i:s'),
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Tên loại công việc', 'Mô tả', 'Trạng thái', 'Ngày tạo'];
    }
}
