<?php

namespace App\Modules\Meeting\Services;

use App\Modules\Meeting\Enums\MeetingStatusEnum;
use App\Modules\Meeting\Models\Meeting;
use Illuminate\Support\Facades\DB;

class MeetingService
{
    public function stats(array $filters): array
    {
        $base = Meeting::filter($filters);

        return [
            'total' => (clone $base)->count(),
            'draft' => (clone $base)->where('status', MeetingStatusEnum::Draft->value)->count(),
            'active' => (clone $base)->where('status', MeetingStatusEnum::Active->value)->count(),
            'in_progress' => (clone $base)->where('status', MeetingStatusEnum::InProgress->value)->count(),
            'ended' => (clone $base)->where('status', MeetingStatusEnum::Ended->value)->count(),
        ];
    }

    public function index(array $filters, int $limit)
    {
        return Meeting::filter($filters)->paginate($limit);
    }

    public function show(Meeting $meeting): Meeting
    {
        return $meeting->load(['agendas', 'participantRecords.user', 'documents', 'conclusions.agenda', 'creator', 'editor']);
    }

    public function store(array $validated): Meeting
    {
        return Meeting::create($validated);
    }

    public function update(Meeting $meeting, array $validated): Meeting
    {
        $meeting->update($validated);

        return $meeting->load(['agendas', 'participantRecords.user', 'documents', 'creator', 'editor']);
    }

    public function destroy(Meeting $meeting): void
    {
        $meeting->delete();
    }

    public function changeStatus(Meeting $meeting, string $status): Meeting
    {
        $current = MeetingStatusEnum::from($meeting->status);
        $next = MeetingStatusEnum::from($status);

        $meeting->update(['status' => $status]);

        return $meeting->fresh();
    }
}
