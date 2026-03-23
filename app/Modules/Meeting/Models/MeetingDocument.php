<?php

namespace App\Modules\Meeting\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MeetingDocument extends Model
{
    protected $table = 'm_meeting_documents';

    protected $fillable = [
        'meeting_id',
        'name',
        'type',
        'file_path',
        'file_type',
        'disk',
        'created_by',
    ];

    protected $casts = [];

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->created_by = auth()->id());

        static::deleting(function (MeetingDocument $doc) {
            if (Storage::disk($doc->disk)->exists($doc->file_path)) {
                Storage::disk($doc->disk)->delete($doc->file_path);
            }
        });
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function personalNotes()
    {
        return $this->hasMany(PersonalNote::class, 'meeting_document_id');
    }

    /** URL công khai đến file */
    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }
}
