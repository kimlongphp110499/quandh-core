<?php

namespace App\Modules\Meeting\Models;

use App\Modules\Core\Models\User;
use App\Modules\Meeting\Enums\MeetingStatusEnum;
use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $table = 'm_meetings';

    protected $fillable = [
        'title',
        'description',
        'location',
        'start_at',
        'end_at',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn ($m) => $m->created_by = $m->updated_by = auth()->id());
        static::updating(fn ($m) => $m->updated_by = auth()->id());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** Đại biểu tham dự (qua bảng pivot m_participants). */
    public function participants()
    {
        return $this->belongsToMany(User::class, 'm_participants', 'meeting_id', 'user_id')
            ->using(Participant::class)
            ->withPivot(['id', 'position', 'meeting_role', 'attendance_status', 'checkin_at', 'absence_reason'])
            ->withTimestamps();
    }

    /** Bản ghi participant đầy đủ (truy vấn trực tiếp). */
    public function participantRecords()
    {
        return $this->hasMany(Participant::class, 'meeting_id');
    }

    public function agendas()
    {
        return $this->hasMany(Agenda::class, 'meeting_id')->orderBy('order_index');
    }

    public function documents()
    {
        return $this->hasMany(MeetingDocument::class, 'meeting_id');
    }

    public function personalNotes()
    {
        return $this->hasMany(PersonalNote::class, 'meeting_id');
    }

    public function votings()
    {
        return $this->hasMany(Voting::class, 'meeting_id');
    }

    /** Danh sách kết luận (1:N). */
    public function conclusions()
    {
        return $this->hasMany(Conclusion::class, 'meeting_id');
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            });
        })->when($filters['status'] ?? null, function ($q, $status) {
            $q->where('status', $status);
        })->when($filters['from_date'] ?? null, function ($q, $from) {
            $q->whereDate('start_at', '>=', $from);
        })->when($filters['to_date'] ?? null, function ($q, $to) {
            $q->whereDate('start_at', '<=', $to);
        })->when($filters['sort_by'] ?? 'start_at', function ($q, $sortBy) use ($filters) {
            $allowed = ['id', 'title', 'start_at', 'end_at', 'status', 'created_at'];
            $col = in_array($sortBy, $allowed) ? $sortBy : 'start_at';
            $q->orderBy($col, $filters['sort_order'] ?? 'desc');
        });
    }
}
