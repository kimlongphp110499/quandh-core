<?php

namespace App\Modules\TaskAssignment\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TaskAssignmentItemDepartment extends Pivot
{
    protected $table = 'task_assignment_item_department';

    protected $fillable = [
        'task_assignment_item_id',
        'department_id',
        'role',
    ];

    public function item()
    {
        return $this->belongsTo(TaskAssignmentItem::class, 'task_assignment_item_id');
    }

    public function department()
    {
        return $this->belongsTo(TaskAssignmentDepartment::class, 'department_id');
    }
}
