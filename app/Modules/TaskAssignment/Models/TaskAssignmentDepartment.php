<?php

namespace App\Modules\TaskAssignment\Models;

use App\Modules\Core\Models\User;
use App\Modules\Core\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Model;

class TaskAssignmentDepartment extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'sort_order',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'sort_order' => 'integer',
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

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('code', 'like', '%'.$search.'%');
            });
        })->when($filters['from_date'] ?? null, function ($query, $fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        })
        ->when($filters['to_date'] ?? null, function ($query, $toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        })
        ->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['sort_by'] ?? 'sort_order', function ($query, $sortBy) use ($filters) {
            $allowed = ['id', 'name', 'code', 'sort_order', 'created_at'];
            $column = in_array($sortBy, $allowed) ? $sortBy : 'sort_order';
            $query->orderBy($column, $filters['sort_order'] ?? 'asc');
        });
    }
}
