<?php

namespace App\Modules\TaskAssignment\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskAssignmentItemUser extends Pivot
{
    protected $table = 'task_assignment_item_user';

    protected $fillable = [
        'task_assignment_item_id',
        'department_id',
        'user_id',
        'assignment_role',
        'assignment_status',
        'assigned_at',
        'accepted_at',
        'completed_at',
        'note',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(TaskAssignmentItem::class, 'task_assignment_item_id');
    }

    public function department()
    {
        return $this->belongsTo(TaskAssignmentDepartment::class, 'department_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
