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
use App\Modules\TaskAssignment\Exports\TaskAssignmentDocumentsTemplateExport;

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
        $newStatus = $validated['status'] ?? null;
        $isTransitioningToIssued = $newStatus === TaskAssignmentDocumentStatusEnum::Issued->value
            && $document->status !== TaskAssignmentDocumentStatusEnum::Issued->value;

        // Khóa chỉnh sửa các trường cốt lõi khi văn bản đã ban hành
        // (chỉ cho phép chuyển về bản nháp qua trường status)
        if ($document->status === TaskAssignmentDocumentStatusEnum::Issued->value) {
            if ($newStatus !== TaskAssignmentDocumentStatusEnum::Draft->value) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => ['Không thể chỉnh sửa văn bản đã ban hành. Vui lòng chuyển về bản nháp trước.'],
                ]);
            }

            // Chỉ cho phép đổi status về draft, bỏ qua các trường khác
            DB::transaction(function () use ($document) {
                $document->update([
                    'status' => TaskAssignmentDocumentStatusEnum::Draft->value,
                    'issued_at' => null,
                ]);
            });

            return $document->load(['type', 'attachments.media', 'creator', 'editor']);
        }

        // Validate bắt buộc khi chuyển từ bản nháp sang ban hành
        if ($isTransitioningToIssued) {
            $errors = $this->validateBeforeIssue($document);
            if (!empty($errors)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => $errors,
                ]);
            }
        }

        DB::transaction(function () use ($document, $validated, $isTransitioningToIssued) {
            $data = collect($validated)->except(['attachment_ids'])->all();

            if ($isTransitioningToIssued) {
                $data['issued_at'] = now();
                // Sinh lịch nhắc ban đầu cho các công việc có hạn
                $this->reminderService->generateRemindersForDocument($document);
            }

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
        $data = ['status' => $status];

        // Khi ban hành, ghi nhận thời điểm ban hành cho từng văn bản chưa có issued_at
        if ($status === TaskAssignmentDocumentStatusEnum::Issued->value) {
            $data['issued_at'] = now();
        }

        // Khi chuyển về nháp, xóa thời điểm ban hành
        if ($status === TaskAssignmentDocumentStatusEnum::Draft->value) {
            $data['issued_at'] = null;
        }

        TaskAssignmentDocument::whereIn('id', $ids)->update($data);
    }

    /**
     * Validate các điều kiện bắt buộc trước khi ban hành văn bản.
     * Trả về danh sách lỗi nếu không đạt, mảng rỗng nếu hợp lệ.
     */
    public function validateBeforeIssue(TaskAssignmentDocument $document): array
    {
        $errors = [];

        // Phải có ít nhất 1 công việc
        $itemsCount = $document->items()->count();
        if ($itemsCount === 0) {
            $errors[] = 'Văn bản phải có ít nhất 1 công việc trước khi ban hành.';
        }

        // Tất cả công việc phải có tên
        $missingName = $document->items()->whereNull('name')->orWhere('name', '')->count();
        if ($missingName > 0) {
            $errors[] = "Có {$missingName} công việc chưa có tên.";
        }

        return $errors;
    }

    public function changeStatus(TaskAssignmentDocument $document, string $status): TaskAssignmentDocument
    {
        // Validate bắt buộc khi ban hành
        if ($status === TaskAssignmentDocumentStatusEnum::Issued->value) {
            $errors = $this->validateBeforeIssue($document);
            if (!empty($errors)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'status' => $errors,
                ]);
            }
        }

        DB::transaction(function () use ($document, $status) {
            $data = ['status' => $status];

            if ($status === TaskAssignmentDocumentStatusEnum::Issued->value) {
                $data['issued_at'] = now();
                // Sinh lịch nhắc ban đầu cho các công việc có hạn
                $this->reminderService->generateRemindersForDocument($document);
            }

            // Khi chuyển về nháp, xóa thời điểm ban hành
            if ($status === TaskAssignmentDocumentStatusEnum::Draft->value) {
                $data['issued_at'] = null;
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
        if ($document->status === TaskAssignmentDocumentStatusEnum::Issued->value) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => ['Không thể thêm tệp đính kèm cho văn bản đã ban hành.'],
            ]);
        }

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
        if ($document->status === TaskAssignmentDocumentStatusEnum::Issued->value) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => ['Không thể xóa tệp đính kèm của văn bản đã ban hành.'],
            ]);
        }

        DB::transaction(function () use ($attachment) {
            $attachment->delete();
        });
    }

    public function sortAttachments(TaskAssignmentDocument $document, array $orderedIds): void
    {
        if ($document->status === TaskAssignmentDocumentStatusEnum::Issued->value) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => ['Không thể sắp xếp tệp đính kèm của văn bản đã ban hành.'],
            ]);
        }

        DB::transaction(function () use ($orderedIds) {
            foreach ($orderedIds as $index => $id) {
                TaskAssignmentDocumentAttachment::where('id', $id)
                    ->update(['sort_order' => $index + 1]);
            }
        });
    }

    /**
     * Tải file Excel mẫu để người dùng điền dữ liệu trước khi import.
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new TaskAssignmentDocumentsTemplateExport, 'mau-import-van-ban-giao-viec.xlsx');
    }
}
