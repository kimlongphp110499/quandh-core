<?php

namespace App\Modules\TaskAssignment\Imports;

use App\Modules\TaskAssignment\Models\TaskAssignmentType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TaskAssignmentTypesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new TaskAssignmentType([
            'name' => $row['ten_loai_van_ban'] ?? $row['name'] ?? null,
            'description' => $row['mo_ta'] ?? $row['description'] ?? null,
            'status' => $row['trang_thai'] ?? $row['status'] ?? 'active',
        ]);
    }
}
