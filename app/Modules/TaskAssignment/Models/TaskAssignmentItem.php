<?php

namespace App\Modules\TaskAssignment\Models;

use App\Modules\Core\Models\User;
use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use App\Modules\TaskAssignment\Enums\TaskDeadlineTypeEnum;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentItem extends Model
{
    protected $fillable = [
        'task_assignment_document_id',
        'name',
        'description',
        'task_assignment_item_type_id',
        'deadline_type',
        'start_at',
        'end_at',
        'processing_status',
        'completion_percent',
        'priority',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'task_assignment_document_id' => 'integer',
        'task_assignment_item_type_id' => 'integer',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'completed_at' => 'datetime',
        'completion_percent' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(fn ($model) => $model->created_by = $model->updated_by = auth()->id());
        static::updating(function ($model) {
            $model->updated_by = auth()->id();
            // Đồng bộ trạng thái tiến độ
            if ($model->isDirty('processing_status') && $model->processing_status === TaskProgressStatusEnum::Done->value) {
                $model->completion_percent = 100;
                $model->completed_at = $model->completed_at ?? now();
            }
            if ($model->isDirty('completion_percent') && (int) $model->completion_percent === 100) {
                $model->processing_status = TaskProgressStatusEnum::Done->value;
                $model->completed_at = $model->completed_at ?? now();
            }
            if ($model->isDirty('processing_status') && $model->processing_status !== TaskProgressStatusEnum::Done->value) {
                $model->completed_at = null;
            }
        });
    }

    public function document()
    {
        return $this->belongsTo(TaskAssignmentDocument::class, 'task_assignment_document_id');
    }

    public function itemType()
    {
        return $this->belongsTo(TaskAssignmentItemType::class, 'task_assignment_item_type_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function departments()
    {
        return $this->belongsToMany(
            TaskAssignmentDepartment::class,
            'task_assignment_item_department',
            'task_assignment_item_id',
            'department_id'
        )->withPivot('role')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'task_assignment_item_user',
            'task_assignment_item_id',
            'user_id'
        )->withPivot(['department_id', 'assignment_role', 'assignment_status', 'assigned_at', 'accepted_at', 'completed_at', 'note'])
            ->withTimestamps();
    }

    public function reports()
    {
        return $this->hasMany(TaskAssignmentItemReport::class, 'task_assignment_item_id');
    }

    /** Lịch sử cập nhật tiến độ (mới nhất trước) */
    public function progressLogs()
    {
        return $this->hasMany(TaskAssignmentProgressLog::class, 'task_assignment_item_id')
            ->latest();
    }

    public function reminders()
    {
        return $this->hasMany(TaskAssignmentReminder::class, 'task_assignment_item_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%');
        })->when($filters['processing_status'] ?? null, function ($query, $status) {
            $query->where('processing_status', $status);
        })->when($filters['priority'] ?? null, function ($query, $priority) {
            $query->where('priority', $priority);
        })->when($filters['deadline_type'] ?? null, function ($query, $deadlineType) {
            $query->where('deadline_type', $deadlineType);
        })->when($filters['task_assignment_document_id'] ?? null, function ($query, $docId) {
            $query->where('task_assignment_document_id', $docId);
        })->when($filters['task_assignment_item_type_id'] ?? null, function ($query, $typeId) {
            $query->where('task_assignment_item_type_id', $typeId);
        })->when($filters['completion_percent_from'] ?? null, function ($query, $val) {
            $query->where('completion_percent', '>=', $val);
        })->when($filters['completion_percent_to'] ?? null, function ($query, $val) {
            $query->where('completion_percent', '<=', $val);
        })->when($filters['start_from'] ?? null, function ($query, $val) {
            $query->where('start_at', '>=', $val);
        })->when($filters['start_to'] ?? null, function ($query, $val) {
            $query->where('start_at', '<=', $val);
        })->when($filters['end_from'] ?? null, function ($query, $val) {
            $query->where('end_at', '>=', $val);
        })->when($filters['end_to'] ?? null, function ($query, $val) {
            $query->where('end_at', '<=', $val);
        })->when($filters['department_id'] ?? null, function ($query, $deptId) {
            $query->whereHas('departments', fn ($q) => $q->where('task_assignment_departments.id', $deptId));
        })->when($filters['user_id'] ?? null, function ($query, $userId) {
            $query->whereHas('users', fn ($q) => $q->where('users.id', $userId));
        })->when($filters['assignment_role'] ?? null, function ($query, $role) {
            $query->whereHas('users', fn ($q) => $q->wherePivot('assignment_role', $role));
        })->when($filters['assignment_status'] ?? null, function ($query, $status) {
            $query->whereHas('users', fn ($q) => $q->wherePivot('assignment_status', $status));
        })->when($filters['from_date'] ?? null, function ($query, $val) {
            $query->whereHas('document', fn ($q) => $q->whereDate('issue_date', '>=', $val));
        })->when($filters['to_date'] ?? null, function ($query, $val) {
            $query->whereHas('document', fn ($q) => $q->whereDate('issue_date', '<=', $val));
        })->when($filters['sort_by'] ?? 'created_at', function ($query, $sortBy) use ($filters) {
            $allowed = ['id', 'created_at', 'updated_at', 'start_at', 'end_at', 'completion_percent', 'priority'];
            $column = in_array($sortBy, $allowed) ? $sortBy : 'created_at';
            $query->orderBy($column, $filters['sort_order'] ?? 'desc');
        })->when($filters['document_issued'] ?? null, function ($query, $val) {
            $query->whereHas('document', fn ($q) => $q->where('status', 'issued'));
        });
    }
}
