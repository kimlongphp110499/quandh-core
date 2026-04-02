<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\TaskAssignment\Exports\TaskAssignmentItemTypesExport;
use App\Modules\TaskAssignment\Imports\TaskAssignmentItemTypesImport;
use App\Modules\TaskAssignment\Models\TaskAssignmentItemType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskAssignmentItemTypeService
{
    public function stats(array $filters): array
    {
        $base = TaskAssignmentItemType::filter($filters);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
        ];
    }

    public function index(array $filters, int $limit)
    {
        return TaskAssignmentItemType::filter($filters)->paginate($limit);
    }

    public function show(TaskAssignmentItemType $itemType): TaskAssignmentItemType
    {
        return $itemType;
    }

    public function store(array $validated): TaskAssignmentItemType
    {
        return TaskAssignmentItemType::create($validated);
    }

    public function update(TaskAssignmentItemType $itemType, array $validated): TaskAssignmentItemType
    {
        $itemType->update($validated);

        return $itemType;
    }

    public function destroy(TaskAssignmentItemType $itemType): void
    {
        $itemType->delete();
    }

    public function bulkDestroy(array $ids): void
    {
        TaskAssignmentItemType::whereIn('id', $ids)->delete();
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        TaskAssignmentItemType::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function changeStatus(TaskAssignmentItemType $itemType, string $status): TaskAssignmentItemType
    {
        $itemType->update(['status' => $status]);

        return $itemType;
    }

    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentItemTypesExport($filters), 'task-assignment-item-types.xlsx');
    }

    public function import($file): void
    {
        Excel::import(new TaskAssignmentItemTypesImport, $file);
    }
}
