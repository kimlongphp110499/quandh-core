<?php

namespace App\Modules\TaskAssignment\Exports;

use App\Modules\TaskAssignment\Models\TaskAssignmentDocument;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaskAssignmentDocumentsExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $filters = []) {}

    public function collection()
    {
        return TaskAssignmentDocument::with(['type', 'creator', 'editor', 'attachments'])
            ->filter($this->filters)
            ->get()
            ->map(fn ($doc) => [
                'id' => $doc->id,
                'name' => $doc->name,
                'summary' => $doc->summary,
                'issue_date' => $doc->issue_date?->format('d/m/Y'),
                'type' => $doc->type?->name ?? '',
                'status' => $doc->status,
                'issued_at' => $doc->issued_at?->format('d/m/Y H:i:s'),
                'attachments' => $doc->attachments->pluck('file_name')->join('; '),
                'created_by' => $doc->creator?->name ?? '',
                'updated_by' => $doc->editor?->name ?? '',
                'created_at' => $doc->created_at?->format('d/m/Y H:i:s'),
                'updated_at' => $doc->updated_at?->format('d/m/Y H:i:s'),
            ]);
    }

    public function headings(): array
    {
        return [
            'ID', 'Tên văn bản', 'Tóm tắt', 'Ngày ban hành', 'Loại văn bản',
            'Trạng thái', 'Thời điểm ban hành', 'Tệp đính kèm',
            'Người tạo', 'Người cập nhật', 'Ngày tạo', 'Ngày cập nhật',
        ];
    }
}
