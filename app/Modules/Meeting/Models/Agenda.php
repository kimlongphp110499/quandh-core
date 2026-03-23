<?php

namespace App\Modules\Meeting\Models;

use Illuminate\Database\Eloquent\Model;

class Agenda extends Model
{
    protected $table = 'm_agendas';

    protected $fillable = [
        'meeting_id',
        'title',
        'description',
        'order_index',
        'duration',
        'is_current',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'duration' => 'integer',
        'is_current' => 'boolean',
    ];

    public function meeting()
    {
        return $this->belongsTo(Meeting::class, 'meeting_id');
    }

    public function speechRequests()
    {
        return $this->hasMany(SpeechRequest::class, 'agenda_id');
    }

    public function votings()
    {
        return $this->hasMany(Voting::class, 'agenda_id');
    }

    public function conclusions()
    {
        return $this->hasMany(Conclusion::class, 'agenda_id');
    }
}
