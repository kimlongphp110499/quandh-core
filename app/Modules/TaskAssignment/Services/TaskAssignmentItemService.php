<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use App\Modules\TaskAssignment\Exports\TaskAssignmentItemsExport;
use App\Modules\TaskAssignment\Imports\TaskAssignmentItemsImport;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskAssignmentItemService
{
    public function stats(array $filters): array
    {
        $base = TaskAssignmentItem::filter($filters);

        return [
            'total' => (clone $base)->count(),
            'todo' => (clone $base)->where('processing_status', TaskProgressStatusEnum::Todo->value)->count(),
            'in_progress' => (clone $base)->where('processing_status', TaskProgressStatusEnum::InProgress->value)->count(),
            'done' => (clone $base)->where('processing_status', TaskProgressStatusEnum::Done->value)->count(),
            'overdue' => (clone $base)->where('processing_status', TaskProgressStatusEnum::Overdue->value)->count(),
            'paused' => (clone $base)->where('processing_status', TaskProgressStatusEnum::Paused->value)->count(),
            'cancelled' => (clone $base)->where('processing_status', TaskProgressStatusEnum::Cancelled->value)->count(),
        ];
    }

    public function index(array $filters, int $limit)
    {
        return TaskAssignmentItem::with(['document', 'itemType', 'departments', 'users'])
            ->filter($filters)
            ->paginate($limit);
    }

    public function show(TaskAssignmentItem $item): TaskAssignmentItem
    {
        return $item->load(['document.type', 'itemType', 'departments', 'users', 'reports.attachments', 'creator', 'editor']);
    }

    public function store(array $validated): TaskAssignmentItem
    {
        return DB::transaction(function () use ($validated) {
            $data = collect($validated)->except(['department_ids', 'user_assignments'])->all();
            $item = TaskAssignmentItem::create($data);

            $this->syncDepartments($item, $validated['department_ids'] ?? []);
            $this->syncUsers($item, $validated['user_assignments'] ?? []);

            return $item->load(['document', 'itemType', 'departments', 'users']);
        });
    }

    public function update(TaskAssignmentItem $item, array $validated): TaskAssignmentItem
    {
        return DB::transaction(function () use ($item, $validated) {
            $data = collect($validated)->except(['department_ids', 'user_assignments'])->all();
            $item->update($data);

            if (array_key_exists('department_ids', $validated)) {
                $this->syncDepartments($item, $validated['department_ids']);
            }

            if (array_key_exists('user_assignments', $validated)) {
                $this->syncUsers($item, $validated['user_assignments']);
            }

            return $item->load(['document', 'itemType', 'departments', 'users']);
        });
    }

    public function destroy(TaskAssignmentItem $item): void
    {
        $item->delete();
    }

    public function bulkDestroy(array $ids): void
    {
        TaskAssignmentItem::whereIn('id', $ids)->delete();
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        TaskAssignmentItem::whereIn('id', $ids)->each(function ($item) use ($status) {
            $item->update(['processing_status' => $status]);
        });
    }

    public function changeStatus(TaskAssignmentItem $item, string $status): TaskAssignmentItem
    {
        $item->update(['processing_status' => $status]);

        return $item->load(['document', 'itemType', 'departments', 'users']);
    }

    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentItemsExport($filters), 'task-assignment-items.xlsx');
    }

    public function import($file): void
    {
        Excel::import(new TaskAssignmentItemsImport, $file);
    }

    public function statsByDepartment(array $filters): array
    {
        return TaskAssignmentItem::with('departments')
            ->filter($filters)
            ->get()
            ->groupBy(fn ($item) => $item->departments->first()?->id)
            ->map(fn ($items, $deptId) => [
                'department_id' => $deptId,
                'department_name' => $items->first()->departments->first()?->name,
                'total' => $items->count(),
                'in_progress' => $items->where('processing_status', TaskProgressStatusEnum::InProgress->value)->count(),
                'done' => $items->where('processing_status', TaskProgressStatusEnum::Done->value)->count(),
                'overdue' => $items->where('processing_status', TaskProgressStatusEnum::Overdue->value)->count(),
            ])
            ->values()
            ->toArray();
    }

    public function statsByUser(array $filters): array
    {
        return TaskAssignmentItem::with('users')
            ->filter($filters)
            ->get()
            ->flatMap(fn ($item) => $item->users->map(fn ($user) => [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'item' => $item,
            ]))
            ->groupBy('user_id')
            ->map(fn ($entries, $userId) => [
                'user_id' => $userId,
                'user_name' => $entries->first()['user_name'],
                'total' => $entries->count(),
                'done' => $entries->filter(fn ($e) => $e['item']->processing_status === TaskProgressStatusEnum::Done->value)->count(),
                'overdue' => $entries->filter(fn ($e) => $e['item']->processing_status === TaskProgressStatusEnum::Overdue->value)->count(),
            ])
            ->values()
            ->toArray();
    }

    public function statsByTime(array $filters): array
    {
        $group = $filters['group_by'] ?? 'month';
        $format = match ($group) {
            'week' => '%Y-%u',
            'quarter' => '%Y-Q',
            default => '%Y-%m',
        };

        return TaskAssignmentItem::filter($filters)
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as period, COUNT(*) as total,
                SUM(processing_status = 'done') as done,
                SUM(processing_status = 'overdue') as overdue")
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    public function overdue(array $filters): mixed
    {
        return TaskAssignmentItem::with(['document', 'departments', 'users'])
            ->where('processing_status', TaskProgressStatusEnum::Overdue->value)
            ->filter($filters)
            ->paginate($filters['limit'] ?? 15);
    }

    public function upcomingDeadline(array $filters): mixed
    {
        $days = (int) ($filters['days'] ?? 3);

        return TaskAssignmentItem::with(['document', 'departments', 'users'])
            ->where('deadline_type', 'has_deadline')
            ->whereNotIn('processing_status', [TaskProgressStatusEnum::Done->value, TaskProgressStatusEnum::Cancelled->value])
            ->whereBetween('end_at', [now(), now()->addDays($days)])
            ->filter($filters)
            ->paginate($filters['limit'] ?? 15);
    }

    private function syncDepartments(TaskAssignmentItem $item, array $departments): void
    {
        $sync = [];
        foreach ($departments as $dept) {
            $sync[$dept['department_id']] = ['role' => $dept['role'] ?? 'main'];
        }
        $item->departments()->sync($sync);
    }

    private function syncUsers(TaskAssignmentItem $item, array $userAssignments): void
    {
        $sync = [];
        foreach ($userAssignments as $assignment) {
            $sync[$assignment['user_id']] = [
                'department_id' => $assignment['department_id'],
                'assignment_role' => $assignment['assignment_role'] ?? 'main',
                'assignment_status' => $assignment['assignment_status'] ?? 'assigned',
                'assigned_at' => now(),
                'note' => $assignment['note'] ?? null,
            ];
        }
        $item->users()->sync($sync);
    }
}
