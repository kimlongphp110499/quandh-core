<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\Core\Services\MediaService;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use App\Modules\TaskAssignment\Models\TaskAssignmentItemReport;
use App\Modules\TaskAssignment\Models\TaskAssignmentItemReportAttachment;
use Illuminate\Support\Facades\DB;

class TaskAssignmentItemReportService
{
    public function __construct(private MediaService $mediaService) {}

    public function index(array $filters, int $limit)
    {
        return TaskAssignmentItemReport::with(['item', 'reporter', 'attachments.media'])
            ->filter($filters)
            ->paginate($limit);
    }

    public function show(TaskAssignmentItemReport $report): TaskAssignmentItemReport
    {
        return $report->load(['item.document', 'reporter', 'attachments.media']);
    }

    public function store(TaskAssignmentItem $item, array $validated, array $files = []): TaskAssignmentItemReport
    {
        $storedFiles = [];

        try {
            return DB::transaction(function () use ($item, $validated, $files, &$storedFiles) {
                $data = collect($validated)->except(['files'])->all();
                $data['task_assignment_item_id'] = $item->id;
                $data['reporter_user_id'] = auth()->id();

                $report = TaskAssignmentItemReport::create($data);

                foreach ($files as $file) {
                    $media = $this->mediaService->uploadOne($report, $file, 'task-assignment-item-report-attachments');
                    $storedFiles[] = ['disk' => $media->disk, 'path' => $media->getPathRelativeToRoot()];

                    TaskAssignmentItemReportAttachment::create([
                        'task_assignment_item_report_id' => $report->id,
                        'media_id' => $media->id,
                        'file_name' => $file->getClientOriginalName(),
                        'sort_order' => 0,
                    ]);
                }

                return $report->load(['reporter', 'attachments.media']);
            });
        } catch (\Throwable $e) {
            $this->mediaService->cleanupStoredFiles($storedFiles);
            throw $e;
        }
    }

    public function update(TaskAssignmentItemReport $report, array $validated, array $files = []): TaskAssignmentItemReport
    {
        $storedFiles = [];

        try {
            return DB::transaction(function () use ($report, $validated, $files, &$storedFiles) {
                $data = collect($validated)->except(['files', 'remove_attachment_ids'])->all();
                $report->update($data);

                if (! empty($validated['remove_attachment_ids'])) {
                    TaskAssignmentItemReportAttachment::whereIn('id', $validated['remove_attachment_ids'])
                        ->where('task_assignment_item_report_id', $report->id)
                        ->delete();
                }

                foreach ($files as $file) {
                    $media = $this->mediaService->uploadOne($report, $file, 'task-assignment-item-report-attachments');
                    $storedFiles[] = ['disk' => $media->disk, 'path' => $media->getPathRelativeToRoot()];

                    $maxSort = $report->attachments()->max('sort_order') ?? 0;
                    TaskAssignmentItemReportAttachment::create([
                        'task_assignment_item_report_id' => $report->id,
                        'media_id' => $media->id,
                        'file_name' => $file->getClientOriginalName(),
                        'sort_order' => $maxSort + 1,
                    ]);
                }

                return $report->load(['reporter', 'attachments.media']);
            });
        } catch (\Throwable $e) {
            $this->mediaService->cleanupStoredFiles($storedFiles);
            throw $e;
        }
    }

    public function destroy(TaskAssignmentItemReport $report): void
    {
        $report->delete();
    }

    public function bulkDestroy(array $ids): void
    {
        TaskAssignmentItemReport::whereIn('id', $ids)->delete();
    }
}
