<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\TaskAssignment\Enums\TaskAssignmentDocumentStatusEnum;
use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use App\Modules\TaskAssignment\Exports\TaskAssignmentItemsExport;
use App\Modules\TaskAssignment\Imports\TaskAssignmentItemsImport;
use App\Modules\TaskAssignment\Models\TaskAssignmentDocument;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use App\Modules\TaskAssignment\Models\TaskAssignmentProgressLog;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Modules\TaskAssignment\Exports\TaskAssignmentItemsTemplateExport;
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

    /**
     * Kiểm tra văn bản giao việc cha đã ban hành chưa.
     * Ném lỗi ValidationException nếu đã ban hành.
     */
    private function guardDocumentNotIssued(TaskAssignmentDocument $document): void
    {
        if ($document->status === TaskAssignmentDocumentStatusEnum::Issued->value) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'document' => ['Không thể thay đổi công việc thuộc văn bản đã ban hành.'],
            ]);
        }
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
        // Kiểm tra văn bản cha chưa ban hành
        $item->loadMissing('document');
        if ($item->document) {
            $this->guardDocumentNotIssued($item->document);
        }

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

    /**
     * Import danh sách công việc từ file Excel theo mẫu template.
     *
     * @param  mixed  $file  File upload từ request
     * @return array{imported: int, failed: int, failures: array}  Kết quả import
     */
    public function import($file): array
    {
        $importer = new TaskAssignmentItemsImport;

        Excel::import($importer, $file);

        $failures = $importer->failures();

        $failureDetails = collect($failures)->map(fn ($f) => [
            'row'     => $f->row(),
            'errors'  => $f->errors(),
            'values'  => $f->values(),
        ])->toArray();

        return [
            'imported' => $importer->getImportedCount(),
            'failed'   => count($failureDetails),
            'failures' => $failureDetails,
        ];
    }

    /**
     * Tải file Excel mẫu để người dùng điền dữ liệu trước khi import.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentItemsTemplateExport, 'mau-import-cong-viec.xlsx');
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
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '{$format}')"))
            ->reorder()
            ->orderBy('period')
            ->get()
            ->toArray();
    }

    /**
     * Lấy danh sách công việc được phân công cho user hiện tại (màn "Công việc của tôi").
     * Tự động thêm điều kiện user_id = user đang đăng nhập, hỗ trợ đầy đủ bộ lọc.
     * Kèm thông tin văn bản, phòng ban, người giao, người phối hợp.
     *
     * @param  array  $filters  Các tiêu chí lọc (đã bao gồm user_id)
     * @param  int    $limit    Số bản ghi mỗi trang
     */
    public function myTasks(array $filters, int $limit)
    {
        return TaskAssignmentItem::with(['document.type', 'itemType', 'departments', 'users', 'creator'])
            ->whereHas('document', fn ($q) => $q->where('status', 'issued'))
            ->filter($filters)
            ->paginate($limit);
    }

    /**
     * Cập nhật tiến độ công việc từ phía người được phân công.
     * Đồng bộ trạng thái tự động qua model boot:
     * - processing_status = done => completion_percent = 100
     * - completion_percent = 100 => processing_status = done
     * - Quá end_at chưa hoàn thành => overdue (được đánh dấu qua scheduler riêng)
     * Đồng thời cập nhật ghi chú trong bảng pivot task_assignment_item_user.
     *
     * @param  TaskAssignmentItem  $item       Công việc cần cập nhật
     * @param  array               $validated  Dữ liệu đã validate: processing_status, completion_percent, note
     */
    /**
     * Cập nhật tiến độ công việc từ phía người được phân công.
     * Đồng thời ghi 1 dòng lịch sử vào task_assignment_progress_logs.
     *
     * @param  TaskAssignmentItem  $item       Công việc cần cập nhật
     * @param  array               $validated  Dữ liệu đã validate: processing_status, completion_percent, note
     */
    public function updateProgress(TaskAssignmentItem $item, array $validated): TaskAssignmentItem
    {
        return DB::transaction(function () use ($item, $validated) {
            // Lưu giá trị cũ trước khi cập nhật để ghi log
            $oldStatus  = $item->processing_status;
            $oldPercent = (int) $item->completion_percent;

            // Cập nhật trạng thái và phần trăm lên bảng item (model boot sẽ đồng bộ tự động)
            $itemData = collect($validated)->only(['processing_status', 'completion_percent'])->all();

            if (! empty($itemData)) {
                $item->update($itemData);
            }

            // Cập nhật ghi chú tiến độ vào pivot của user hiện tại
            if (isset($validated['note'])) {
                $item->users()->updateExistingPivot(auth()->id(), [
                    'note' => $validated['note'],
                ]);
            }

            // Ghi lịch sử cập nhật tiến độ (sau khi model boot đã đồng bộ)
            $item->refresh();
            TaskAssignmentProgressLog::create([
                'task_assignment_item_id' => $item->id,
                'user_id'                 => auth()->id(),
                'old_processing_status'   => $oldStatus,
                'new_processing_status'   => $item->processing_status,
                'old_completion_percent'  => $oldPercent,
                'new_completion_percent'  => (int) $item->completion_percent,
                'note'                    => $validated['note'] ?? null,
            ]);

            return $item->load(['document.type', 'itemType', 'departments', 'users', 'creator']);
        });
    }

    /**
     * Lấy danh sách lịch sử cập nhật tiến độ của một công việc.
     * Trả về mới nhất trước, kèm thông tin người cập nhật.
     *
     * @param  TaskAssignmentItem  $item  Công việc cần xem lịch sử
     */
    public function getProgressHistory(TaskAssignmentItem $item)
    {
        return $item->progressLogs()->with('user')->get();
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
