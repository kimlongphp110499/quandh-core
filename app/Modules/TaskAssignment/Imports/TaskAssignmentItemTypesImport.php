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
            'name' => $row['ten_loai_cong_viec'] ?? $row['name'] ?? null,
            'description' => $row['mo_ta'] ?? $row['description'] ?? null,
            'status' => $row['trang_thai'] ?? $row['status'] ?? 'active',
        ]);
    }
}
