<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource trả về 1 bản ghi lịch sử cập nhật tiến độ công việc.
 */
class TaskAssignmentProgressLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'task_assignment_item_id' => $this->task_assignment_item_id,
            'user_id'                 => $this->user_id,
            'user_name'               => $this->whenLoaded('user', fn () => $this->user?->name),
            'old_processing_status'   => $this->old_processing_status,
            'new_processing_status'   => $this->new_processing_status,
            'old_completion_percent'  => $this->old_completion_percent,
            'new_completion_percent'  => $this->new_completion_percent,
            'note'                    => $this->note,
            'created_at'              => $this->created_at?->format('H:i:s d/m/Y'),
        ];
    }
}
