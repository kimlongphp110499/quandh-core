<?php

namespace App\Modules\TaskAssignment\Imports;

use App\Modules\TaskAssignment\Models\TaskAssignmentItemType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TaskAssignmentItemTypesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new TaskAssignmentItemType([
            'name' => $row['Tên loại công việc'] ?? $row['name'] ?? null,
            'description' => $row['Mô tả'] ?? $row['description'] ?? null,
            'status' => $row['Trạng thái (active/inactive)'] ?? $row['status'] ?? 'active',
        ]);
    }
}
