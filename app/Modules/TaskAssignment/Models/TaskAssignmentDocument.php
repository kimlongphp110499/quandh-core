<?php

namespace App\Modules\TaskAssignment\Models;

use App\Modules\Core\Models\User;
use App\Modules\TaskAssignment\Enums\TaskAssignmentDocumentStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TaskAssignmentDocument extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'summary',
        'issue_date',
        'task_assignment_type_id',
        'status',
        'issued_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'issued_at' => 'datetime',
        'task_assignment_type_id' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(fn ($model) => $model->created_by = $model->updated_by = auth()->id());
        static::updating(fn ($model) => $model->updated_by = auth()->id());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function type()
    {
        return $this->belongsTo(TaskAssignmentType::class, 'task_assignment_type_id');
    }

    public function items()
    {
        return $this->hasMany(TaskAssignmentItem::class, 'task_assignment_document_id');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAssignmentDocumentAttachment::class, 'task_assignment_document_id')->orderBy('sort_order');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%');
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['task_assignment_type_id'] ?? null, function ($query, $typeId) {
            $query->where('task_assignment_type_id', $typeId);
        })->when($filters['from_date'] ?? null, function ($query, $fromDate) {
            $query->whereDate('issue_date', '>=', $fromDate);
        })->when($filters['to_date'] ?? null, function ($query, $toDate) {
            $query->whereDate('issue_date', '<=', $toDate);
        })->when($filters['sort_by'] ?? 'created_at', function ($query, $sortBy) use ($filters) {
            $allowed = ['id', 'name', 'issue_date', 'created_at', 'updated_at'];
            $column = in_array($sortBy, $allowed) ? $sortBy : 'created_at';
            $query->orderBy($column, $filters['sort_order'] ?? 'desc');
        });
    }
}
