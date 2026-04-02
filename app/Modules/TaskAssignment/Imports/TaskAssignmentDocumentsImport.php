<?php

namespace App\Modules\TaskAssignment\Imports;

use App\Modules\TaskAssignment\Models\TaskAssignmentDocument;
use App\Modules\TaskAssignment\Models\TaskAssignmentType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TaskAssignmentDocumentsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $typeId = null;
        if (! empty($row['loai_van_ban']) || ! empty($row['task_assignment_type'])) {
            $typeName = $row['loai_van_ban'] ?? $row['task_assignment_type'];
            $type = TaskAssignmentType::where('name', $typeName)->first();
            $typeId = $type?->id;
        }

        return new TaskAssignmentDocument([
            'name' => $row['ten_van_ban'] ?? $row['name'] ?? null,
            'summary' => $row['tom_tat'] ?? $row['summary'] ?? null,
            'issue_date' => $row['ngay_ban_hanh'] ?? $row['issue_date'] ?? null,
            'task_assignment_type_id' => $typeId,
            'status' => 'draft',
        ]);
    }
}
