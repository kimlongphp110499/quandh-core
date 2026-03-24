<?php

namespace App\Modules\Meeting\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MeetingDocument extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'm_meeting_documents';

    protected $fillable = [
        'meeting_id',
        'name',
        'type',
        'created_by',
    ];

    protected $casts = [];

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->created_by = auth()->id());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')->singleFile();
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
        return $this->getFirstMediaUrl('file');
    }
}
