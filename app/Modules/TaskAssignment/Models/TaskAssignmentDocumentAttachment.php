<?php

namespace App\Modules\TaskAssignment\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentDocumentAttachment extends Model
{
    protected $fillable = [
        'task_assignment_document_id',
        'media_id',
        'file_name',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'task_assignment_document_id' => 'integer',
        'media_id' => 'integer',
        'sort_order' => 'integer',
    ];

    protected static function booted()
    {
        static::creating(fn ($model) => $model->created_by = $model->updated_by = auth()->id());
        static::updating(fn ($model) => $model->updated_by = auth()->id());
    }

    public function document()
    {
        return $this->belongsTo(TaskAssignmentDocument::class, 'task_assignment_document_id');
    }

    public function media()
    {
        return $this->belongsTo(\Spatie\MediaLibrary\MediaCollections\Models\Media::class, 'media_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
