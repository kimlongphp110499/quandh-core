<?php

namespace App\Modules\TaskAssignment\Exports;

use App\Modules\TaskAssignment\Models\TaskAssignmentType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaskAssignmentTypesExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $filters = []) {}

    public function collection()
    {
        return TaskAssignmentType::filter($this->filters)->get()->map(fn ($type) => [
            'id' => $type->id,
            'name' => $type->name,
            'description' => $type->description,
            'status' => $type->status,
            'created_at' => $type->created_at?->format('d/m/Y H:i:s'),
        ]);
    }

    public function headings(): array
    {
        return ['ID', 'Tên loại văn bản', 'Mô tả', 'Trạng thái', 'Ngày tạo'];
    }
}
