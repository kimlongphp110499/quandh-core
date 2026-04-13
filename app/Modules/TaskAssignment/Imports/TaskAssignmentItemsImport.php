<?php

namespace App\Modules\TaskAssignment\Imports;

use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

/**
 * Import danh sách công việc từ file Excel theo đúng mẫu template.
 *
 * Cấu trúc file mẫu (TaskAssignmentItemsTemplateExport):
 *   - Hàng 1 (heading row): key kỹ thuật – name, task_assignment_item_type_id,
 *                            task_assignment_document_id, description, deadline_type,
 *                            start_at, end_at, processing_status, completion_percent, priority
 *   - Hàng 2+: dữ liệu người dùng điền vào
 *
 * Các cột ID (task_assignment_item_type_id, task_assignment_document_id) nhập trực tiếp
 * là giá trị số nguyên. Để trống nếu không liên kết.
 *
 * Ngày (start_at, end_at) nhập theo định dạng Y-m-d hoặc d/m/Y.
 */
class TaskAssignmentItemsImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading,
    SkipsOnFailure
{
    use SkipsFailures;

    /**
     * Số dòng import thành công.
     */
    protected int $importedCount = 0;

    /**
     * Xử lý từng batch dữ liệu đã qua validation.
     */
    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            TaskAssignmentItem::create([
                'task_assignment_document_id' => $this->parseId($row['task_assignment_document_id']),
                'task_assignment_item_type_id' => $this->parseId($row['task_assignment_item_type_id']),
                'name'                => $row['name'],
                'description'         => $row['description'] ?? null,
                'deadline_type'       => $row['deadline_type'] ?? 'no_deadline',
                'start_at'            => $this->parseDate($row['start_at'] ?? null),
                'end_at'              => $this->parseDate($row['end_at'] ?? null),
                'processing_status'   => $row['processing_status'] ?? 'todo',
                'completion_percent'  => $this->parsePercent($row['completion_percent'] ?? null),
                'priority'            => $row['priority'] ?? 'medium',
            ]);

            $this->importedCount++;
        }
    }

    /**
     * Rule validate cho từng dòng dữ liệu.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'task_assignment_document_id' => ['nullable', 'integer'],

            'task_assignment_item_type_id' => ['nullable', 'integer'],

            'description' => ['nullable', 'string'],

            'deadline_type' => [
                'nullable',
                Rule::in(['has_deadline', 'no_deadline']),
            ],

            'start_at' => ['nullable', 'string'],

            'end_at' => ['nullable', 'string'],

            'processing_status' => [
                'nullable',
                Rule::in(['todo', 'in_progress', 'done', 'overdue', 'paused', 'cancelled']),
            ],

            'completion_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],

            'priority' => [
                'nullable',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
        ];
    }

    /**
     * Thông báo lỗi validate thân thiện.
     */
    public function customValidationMessages(): array
    {
        return [
            'name.required'            => 'Tên công việc là bắt buộc.',
            'name.max'                 => 'Tên công việc không được vượt quá 255 ký tự.',
            'deadline_type.in'         => 'Loại thời hạn phải là has_deadline hoặc no_deadline.',
            'processing_status.in'     => 'Trạng thái xử lý không hợp lệ (todo, in_progress, done, overdue, paused, cancelled).',
            'priority.in'              => 'Mức độ ưu tiên không hợp lệ (low, medium, high, urgent).',
            'completion_percent.min'   => 'Phần trăm hoàn thành không được nhỏ hơn 0.',
            'completion_percent.max'   => 'Phần trăm hoàn thành không được lớn hơn 100.',
        ];
    }

    /**
     * Kích thước batch insert để tối ưu hiệu năng.
     */
    public function batchSize(): int
    {
        return 100;
    }

    /**
     * Kích thước chunk đọc file để giảm bộ nhớ.
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Lấy số dòng đã import thành công.
     */
    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Parse giá trị ID – trả về null nếu rỗng hoặc không phải số nguyên dương.
     */
    private function parseId(mixed $value): ?int
    {
        if ($value === null || $value === '' || $value === 0) {
            return null;
        }

        $id = (int) $value;

        return $id > 0 ? $id : null;
    }

    /**
     * Parse chuỗi ngày sang định dạng Y-m-d.
     * Hỗ trợ Y-m-d và d/m/Y.
     */
    private function parseDate(mixed $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        // Định dạng serial number của Excel (số nguyên)
        if (is_numeric($value) && strlen((string) $value) <= 5) {
            try {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value);

                return $date->format('Y-m-d');
            } catch (\Exception) {
                return null;
            }
        }

        $str = trim((string) $value);

        // Định dạng d/m/Y
        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $str, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        // Định dạng Y-m-d (đã đúng)
        if (preg_match('/^\d{4}-\d{2}-\d{2}/', $str)) {
            return substr($str, 0, 10);
        }

        return null;
    }

    /**
     * Parse giá trị phần trăm, đảm bảo trong khoảng 0–100.
     */
    private function parsePercent(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        return max(0, min(100, (int) $value));
    }
}
