<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\TaskAssignment\Exports\TaskAssignmentDepartmentsExport;
use App\Modules\TaskAssignment\Exports\TaskAssignmentDepartmentsTemplateExport;
use App\Modules\TaskAssignment\Imports\TaskAssignmentDepartmentsImport;
use App\Modules\TaskAssignment\Models\TaskAssignmentDepartment;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskAssignmentDepartmentService
{
    public function stats(array $filters): array
    {
        $base = TaskAssignmentDepartment::filter($filters);

        return [
            'total' => (clone $base)->count(),
            'active' => (clone $base)->where('status', 'active')->count(),
            'inactive' => (clone $base)->where('status', 'inactive')->count(),
        ];
    }

    public function index(array $filters, int $limit)
    {
        return TaskAssignmentDepartment::filter($filters)->paginate($limit);
    }

    public function show(TaskAssignmentDepartment $department): TaskAssignmentDepartment
    {
        return $department;
    }

    public function store(array $validated): TaskAssignmentDepartment
    {
        return TaskAssignmentDepartment::create($validated);
    }

    public function update(TaskAssignmentDepartment $department, array $validated): TaskAssignmentDepartment
    {
        $department->update($validated);

        return $department;
    }

    public function destroy(TaskAssignmentDepartment $department): void
    {
        $department->delete();
    }

    public function bulkDestroy(array $ids): void
    {
        TaskAssignmentDepartment::whereIn('id', $ids)->delete();
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        TaskAssignmentDepartment::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function changeStatus(TaskAssignmentDepartment $department, string $status): TaskAssignmentDepartment
    {
        $department->update(['status' => $status]);

        return $department;
    }

    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentDepartmentsExport($filters), 'task-assignment-departments.xlsx');
    }

    public function import($file): void
    {
        Excel::import(new TaskAssignmentDepartmentsImport, $file);
    }

    /**
     * Tải file Excel mẫu để người dùng điền dữ liệu phòng ban trước khi import.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentDepartmentsTemplateExport, 'mau-import-phong-ban.xlsx');
    }
}
