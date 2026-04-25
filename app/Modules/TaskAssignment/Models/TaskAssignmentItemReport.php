<?php

namespace App\Modules\TaskAssignment\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class TaskAssignmentItemReport extends Model implements HasMedia
{
    use InteractsWithMedia;
    protected $fillable = [
        'task_assignment_item_id',
        'reporter_user_id',
        'completed_at',
        'report_document_number',
        'report_document_excerpt',
        'report_document_content',
    ];

    protected $casts = [
        'task_assignment_item_id' => 'integer',
        'reporter_user_id' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function item()
    {
        return $this->belongsTo(TaskAssignmentItem::class, 'task_assignment_item_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    public function attachments()
    {
        return $this->hasMany(TaskAssignmentItemReportAttachment::class, 'task_assignment_item_report_id')->orderBy('sort_order');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['task_assignment_item_id'] ?? null, function ($query, $itemId) {
            $query->where('task_assignment_item_id', $itemId);
        })->when($filters['reporter_user_id'] ?? null, function ($query, $userId) {
            $query->where('reporter_user_id', $userId);
        })->when(!empty($filters['only_mine']), function ($query) {
            $query->where('reporter_user_id', auth()->id());
        })->when($filters['sort_by'] ?? 'created_at', function ($query, $sortBy) use ($filters) {
            $allowed = ['id', 'created_at', 'completed_at'];
            $column = in_array($sortBy, $allowed) ? $sortBy : 'created_at';
            $query->orderBy($column, $filters['sort_order'] ?? 'desc');
        });
    }
}
