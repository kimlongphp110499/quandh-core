<?php

namespace App\Modules\Meeting\Models;

use Illuminate\Database\Eloquent\Model;

class SpeechRequest extends Model
{
    protected $table = 'm_speech_requests';

    protected $fillable = [
        'participant_id',
        'agenda_id',
        'content',
        'status',
        'rejection_reason',
    ];

    public function participant()
    {
        return $this->belongsTo(Participant::class, 'participant_id');
    }

    public function agenda()
    {
        return $this->belongsTo(Agenda::class, 'agenda_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when($filters['agenda_id'] ?? null, fn ($q, $id) => $q->where('agenda_id', $id));
    }
}
