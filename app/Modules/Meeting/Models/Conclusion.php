<?php

namespace App\Modules\Meeting\Models;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Model;

class Conclusion extends Model
{
    protected $table = 'm_conclusions';

    protected $fillable = [
        'meeting_id',
        'agenda_id',
        'title',
        'content',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($c) => $c->created_by = $c->updated_by = auth()->id());
        static::updating(fn ($c) => $c->updated_by = auth()->id());
    }

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'agenda_id');
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
