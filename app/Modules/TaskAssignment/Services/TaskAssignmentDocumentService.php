<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\TaskAssignment\Enums\TaskAssignmentDocumentStatusEnum;
use App\Modules\TaskAssignment\Enums\TaskDeadlineTypeEnum;
use App\Modules\TaskAssignment\Exports\TaskAssignmentDocumentsExport;
use App\Modules\TaskAssignment\Imports\TaskAssignmentDocumentsImport;
use App\Modules\TaskAssignment\Models\TaskAssignmentDocument;
use App\Modules\TaskAssignment\Models\TaskAssignmentDocumentAttachment;
use App\Modules\Core\Services\MediaService;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskAssignmentDocumentService
{
    public function __construct(
        private MediaService $mediaService,
        private TaskAssignmentReminderService $reminderService,
    ) {}

    public function stats(array $filters): array
    {
        $base = TaskAssignmentDocument::filter($filters);

        return [
            'total' => (clone $base)->count(),
            'draft' => (clone $base)->where('status', TaskAssignmentDocumentStatusEnum::Draft->value)->count(),
            'issued' => (clone $base)->where('status', TaskAssignmentDocumentStatusEnum::Issued->value)->count(),
        ];
    }

    public function index(array $filters, int $limit)
    {
        return TaskAssignmentDocument::with(['type', 'creator', 'editor'])
            ->filter($filters)
            ->paginate($limit);
    }

    public function show(TaskAssignmentDocument $document): TaskAssignmentDocument
    {
        return $document->load(['type', 'attachments.media', 'creator', 'editor']);
    }

    public function store(array $validated): TaskAssignmentDocument
    {
        return DB::transaction(function () use ($validated) {
            $data = collect($validated)->except(['attachment_ids'])->all();

            return TaskAssignmentDocument::create($data);
        });
    }

    public function update(TaskAssignmentDocument $document, array $validated): TaskAssignmentDocument
    {
        DB::transaction(function () use ($document, $validated) {
            $data = collect($validated)->except(['attachment_ids'])->all();
            $document->update($data);
        });

        return $document->load(['type', 'attachments.media', 'creator', 'editor']);
    }

    public function destroy(TaskAssignmentDocument $document): void
    {
        $document->delete();
    }

    public function bulkDestroy(array $ids): void
    {
        TaskAssignmentDocument::whereIn('id', $ids)->delete();
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        TaskAssignmentDocument::whereIn('id', $ids)->update(['status' => $status]);
    }

    public function changeStatus(TaskAssignmentDocument $document, string $status): TaskAssignmentDocument
    {
        DB::transaction(function () use ($document, $status) {
            $data = ['status' => $status];

            if ($status === TaskAssignmentDocumentStatusEnum::Issued->value) {
                $data['issued_at'] = now();
                // Sinh lịch nhắc ban đầu cho các công việc có hạn
                $this->reminderService->generateRemindersForDocument($document);
            }

            $document->update($data);
        });

        return $document->load(['type', 'attachments.media', 'creator', 'editor']);
    }

    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentDocumentsExport($filters), 'task-assignment-documents.xlsx');
    }

    public function import($file): void
    {
        Excel::import(new TaskAssignmentDocumentsImport, $file);
    }

    public function addAttachments(TaskAssignmentDocument $document, array $files): TaskAssignmentDocument
    {
        $storedFiles = [];

        try {
            DB::transaction(function () use ($document, $files, &$storedFiles) {
                foreach ($files as $file) {
                    $media = $this->mediaService->uploadOne($document, $file, 'task-assignment-document-attachments');

                    // Lưu lại để cleanup nếu transaction rollback
                    $storedFiles[] = [
                        'disk' => $media->disk,
                        'path' => $media->getPathRelativeToRoot(),
                    ];

                    $maxSort = $document->attachments()->max('sort_order') ?? 0;

                    TaskAssignmentDocumentAttachment::create([
                        'task_assignment_document_id' => $document->id,
                        'media_id' => $media->id,
                        'file_name' => $file->getClientOriginalName(),
                        'sort_order' => $maxSort + 1,
                    ]);
                }
            });
        } catch (\Throwable $e) {
            $this->mediaService->cleanupStoredFiles($storedFiles);
            throw $e;
        }

        return $document->load('attachments.media');
    }

    public function removeAttachment(TaskAssignmentDocument $document, TaskAssignmentDocumentAttachment $attachment): void
    {
        DB::transaction(function () use ($attachment) {
            $attachment->delete();
        });
    }

    public function sortAttachments(TaskAssignmentDocument $document, array $orderedIds): void
    {
        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                TaskAssignmentDocumentAttachment::where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }
}
