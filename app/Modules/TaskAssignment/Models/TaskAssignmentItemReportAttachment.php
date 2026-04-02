<?php

namespace App\Modules\TaskAssignment\Models;

use Illuminate\Database\Eloquent\Model;

class TaskAssignmentItemReportAttachment extends Model
{
    protected $fillable = [
        'task_assignment_item_report_id',
        'media_id',
        'file_name',
        'sort_order',
    ];

    protected $casts = [
        'task_assignment_item_report_id' => 'integer',
        'media_id' => 'integer',
        'sort_order' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(TaskAssignmentItemReport::class, 'task_assignment_item_report_id');
    }

    public function media()
    {
        return $this->belongsTo(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'media_id');
    }
}
