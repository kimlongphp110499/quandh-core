<?php

namespace App\Modules\TaskAssignment\Imports;

use App\Modules\TaskAssignment\Models\TaskAssignmentDocument;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use App\Modules\TaskAssignment\Models\TaskAssignmentItemType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TaskAssignmentItemsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $documentId = null;
        if (! empty($row['van_ban']) || ! empty($row['document_code_or_name'])) {
            $docName = $row['van_ban'] ?? $row['document_code_or_name'];
            $doc = TaskAssignmentDocument::where('name', $docName)->first();
            $documentId = $doc?->id;
        }

        $itemTypeId = null;
        if (! empty($row['loai_cong_viec']) || ! empty($row['task_name'])) {
            $typeName = $row['loai_cong_viec'] ?? null;
            if ($typeName) {
                $type = TaskAssignmentItemType::where('name', $typeName)->first();
                $itemTypeId = $type?->id;
            }
        }

        $deadlineType = $row['loai_thoi_han'] ?? $row['deadline_type'] ?? 'no_deadline';

        return new TaskAssignmentItem([
            'task_assignment_document_id' => $documentId,
            'name' => $row['ten_cong_viec'] ?? $row['task_name'] ?? null,
            'description' => $row['mo_ta'] ?? $row['description'] ?? null,
            'task_assignment_item_type_id' => $itemTypeId,
            'deadline_type' => $deadlineType,
            'start_at' => $row['bat_dau'] ?? $row['start_at'] ?? null,
            'end_at' => $row['ket_thuc'] ?? $row['end_at'] ?? null,
            'processing_status' => 'todo',
            'completion_percent' => 0,
            'priority' => $row['uu_tien'] ?? $row['priority'] ?? 'medium',
        ]);
    }
}
