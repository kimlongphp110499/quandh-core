<?php

namespace App\Modules\TaskAssignment\Imports;

use App\Modules\TaskAssignment\Models\TaskAssignmentDepartment;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TaskAssignmentDepartmentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new TaskAssignmentDepartment([
            'code' => $row['ma'] ?? $row['code'] ?? null,
            'name' => $row['ten_phong_ban'] ?? $row['name'] ?? null,
            'description' => $row['mo_ta'] ?? $row['description'] ?? null,
            'status' => $row['trang_thai'] ?? $row['status'] ?? 'active',
            'sort_order' => (int) ($row['thu_tu'] ?? $row['sort_order'] ?? 0),
        ]);
    }
}
