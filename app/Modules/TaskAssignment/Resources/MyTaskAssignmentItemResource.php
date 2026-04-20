<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource trả về thông tin công việc trong màn "Công việc của tôi".
 * Kèm thêm thông tin pivot của user hiện tại (vai trò, trạng thái giao việc, ghi chú),
 * cảnh báo deadline màu sắc theo nghiệp vụ.
 */
class MyTaskAssignmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Thông tin pivot của user hiện tại trong danh sách users
        $myPivot = $this->whenLoaded('users', function () {
            $me = $this->users->firstWhere('id', auth()->id());

            return $me ? [
                'assignment_role'   => $me->pivot->assignment_role,
                'assignment_status' => $me->pivot->assignment_status,
                'assigned_at'       => $me->pivot->assigned_at,
                'accepted_at'       => $me->pivot->accepted_at,
                'completed_at'      => $me->pivot->completed_at,
                'note'              => $me->pivot->note,
            ] : null;
        });

        return [
            'id'                            => $this->id,
            'task_assignment_document_id'   => $this->task_assignment_document_id,
            'document'                      => $this->whenLoaded('document', fn () => [
                'id'         => $this->document->id,
                'name'       => $this->document->name,
                'issue_date' => $this->document->issue_date?->format('d/m/Y'),
                'type'       => $this->document->relationLoaded('type') ? $this->document->type?->name : null,
            ]),
            'name'                          => $this->name,
            'description'                   => $this->description,
            'item_type'                     => $this->whenLoaded('itemType', fn () => $this->itemType?->name),
            'deadline_type'                 => $this->deadline_type,
            'start_at'                      => $this->start_at?->format('d/m/Y'),
            'end_at'                        => $this->end_at?->format('d/m/Y'),
            'processing_status'             => $this->processing_status,
            'completion_percent'            => (int) $this->completion_percent,
            'priority'                      => $this->priority,
            'completed_at'                  => $this->completed_at?->format('d/m/Y H:i:s'),
            // Cảnh báo deadline màu theo nghiệp vụ: đỏ < 2 ngày, vàng < 4 ngày, xanh >= 5 ngày
            'days_remaining'                => $this->end_at && $this->deadline_type === 'has_deadline'
                ? max(0, (int) now()->startOfDay()->diffInDays($this->end_at->startOfDay(), false))
                : null,
            'deadline_alert_color'          => $this->resolveDeadlineAlertColor(),
            // Danh sách phòng ban thực hiện (chính + phối hợp)
            'departments'                   => $this->whenLoaded('departments', fn () => $this->departments->map(fn ($dept) => [
                'id'   => $dept->id,
                'code' => $dept->code,
                'name' => $dept->name,
                'role' => $dept->pivot->role,
            ])),
            // Danh sách người thực hiện (để FE biết ai chủ trì, ai hỗ trợ)
            'users'                         => $this->whenLoaded('users', fn () => $this->users->map(fn ($user) => [
                'id'                => $user->id,
                'name'              => $user->name,
                'department_id'     => $user->pivot->department_id,
                'assignment_role'   => $user->pivot->assignment_role,
                'assignment_status' => $user->pivot->assignment_status,
            ])),
            // Thông tin pivot riêng của user hiện tại
            'my_assignment'                 => $myPivot,
            'created_by'                    => $this->whenLoaded('creator', fn () => $this->creator?->name, $this->created_by),
            'created_at'                    => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at'                    => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Tính màu cảnh báo deadline dựa trên số ngày còn lại.
     * - Đỏ: dưới 2 ngày (hoặc đã quá hạn)
     * - Vàng: dưới 4 ngày
     * - Xanh: từ 5 ngày trở lên
     * - null: không có thời hạn hoặc đã hoàn thành
     */
    private function resolveDeadlineAlertColor(): ?string
    {
        if ($this->deadline_type !== 'has_deadline' || ! $this->end_at) {
            return null;
        }

        // Đã hoàn thành thì không cần cảnh báo
        if (in_array($this->processing_status, ['done', 'cancelled'])) {
            return null;
        }

        $daysRemaining = (int) now()->startOfDay()->diffInDays($this->end_at->startOfDay(), false);

        if ($daysRemaining < 2) {
            return 'red';
        }

        if ($daysRemaining < 4) {
            return 'yellow';
        }

        return 'green';
    }
}
