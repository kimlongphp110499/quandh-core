<?php

namespace App\Modules\TaskAssignment\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Model lịch sử cập nhật tiến độ công việc.
 * Ghi lại mỗi lần user cập nhật trạng thái / phần trăm / ghi chú tiến độ.
 */
class TaskAssignmentProgressLog extends Model
{
    protected $fillable = [
        'task_assignment_item_id',
        'user_id',
        'old_processing_status',
        'new_processing_status',
        'old_completion_percent',
        'new_completion_percent',
        'note',
    ];

    protected $casts = [
        'task_assignment_item_id' => 'integer',
        'user_id'                 => 'integer',
        'old_completion_percent'  => 'integer',
        'new_completion_percent'  => 'integer',
    ];

    /** Công việc được cập nhật tiến độ */
    public function item()
    {
        return $this->belongsTo(TaskAssignmentItem::class, 'task_assignment_item_id');
    }

    /** Người thực hiện cập nhật */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
