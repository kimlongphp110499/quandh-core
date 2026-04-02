<?php

namespace App\Modules\TaskAssignment\Exports;

use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TaskAssignmentItemsExport implements FromCollection, WithHeadings
{
    public function __construct(protected array $filters = []) {}

    public function collection()
    {
        return TaskAssignmentItem::with(['document', 'itemType', 'departments', 'users'])
            ->filter($this->filters)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'document' => $item->document?->name ?? '',
                'name' => $item->name,
                'description' => $item->description,
                'item_type' => $item->itemType?->name ?? '',
                'deadline_type' => $item->deadline_type,
                'start_at' => $item->start_at?->format('d/m/Y H:i:s'),
                'end_at' => $item->end_at?->format('d/m/Y H:i:s'),
                'processing_status' => $item->processing_status,
                'completion_percent' => $item->completion_percent,
                'priority' => $item->priority,
                'departments' => $item->departments->map(fn ($d) => $d->name.'('.$d->pivot->role.')')->join('; '),
                'users' => $item->users->map(fn ($u) => $u->name.'('.$u->pivot->assignment_role.')')->join('; '),
                'created_at' => $item->created_at?->format('d/m/Y H:i:s'),
                'updated_at' => $item->updated_at?->format('d/m/Y H:i:s'),
            ]);
    }

    public function headings(): array
    {
        return [
            'ID', 'Văn bản', 'Tên công việc', 'Mô tả', 'Loại công việc',
            'Loại thời hạn', 'Bắt đầu', 'Kết thúc',
            'Trạng thái xử lý', '% hoàn thành', 'Ưu tiên',
            'Phòng ban', 'Người thực hiện',
            'Ngày tạo', 'Ngày cập nhật',
        ];
    }
}
