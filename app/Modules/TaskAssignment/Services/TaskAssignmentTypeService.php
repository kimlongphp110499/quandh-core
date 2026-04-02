<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\TaskAssignment\Exports\TaskAssignmentTypesExport;
use App\Modules\TaskAssignment\Imports\TaskAssignmentTypesImport;
use App\Modules\TaskAssignment\Models\TaskAssignmentType;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskAssignmentTypeService
{
    public function stats(array $filters): array
    {
        $base = TaskAssignmentType::filter($filters);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
        ];
    }

    public function index(array $filters, int $limit)
    {
        return TaskAssignmentType::filter($filters)->paginate($limit);
    }

    public function show(TaskAssignmentType $type): TaskAssignmentType
    {
        return $type;
    }

    public function store(array $validated): TaskAssignmentType
    {
        return TaskAssignmentType::create($validated);
    }

    public function update(TaskAssignmentType $type, array $validated): TaskAssignmentType
    {
        $type->update($validated);

        return $type;
    }

    public function destroy(TaskAssignmentType $type): void
    {
        $type->delete();
    }

    public function bulkDestroy(array $ids): void
    {
        TaskAssignmentType::whereIn('id', $ids)->delete();
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        TaskAssignmentType::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function changeStatus(TaskAssignmentType $type, string $status): TaskAssignmentType
    {
        $type->update(['status' => $status]);

        return $type;
    }

    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentTypesExport($filters), 'task-assignment-types.xlsx');
    }

    public function import($file): void
    {
        Excel::import(new TaskAssignmentTypesImport, $file);
    }
}
