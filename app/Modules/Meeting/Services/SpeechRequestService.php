<?php

namespace App\Modules\Meeting\Services;

use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Models\Participant;
use App\Modules\Meeting\Models\SpeechRequest;

class SpeechRequestService
{
    public function index(Meeting $meeting, array $filters): \Illuminate\Database\Eloquent\Collection
    {
        return SpeechRequest::with(['participant.user', 'agenda'])
            ->whereHas('participant', fn ($q) => $q->where('meeting_id', $meeting->id))
            ->filter($filters)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Đại biểu đăng ký phát biểu.
     */
    public function store(Meeting $meeting, array $validated): SpeechRequest
    {
        $participant = Participant::where('meeting_id', $meeting->id)
            ->where('user_id', auth()->id())
            ->first();

        return SpeechRequest::create(array_merge($validated, [
            'participant_id' => $participant->id,
            'status' => 'pending',
        ]))->load(['participant.user', 'agenda']);
    }

    /**
     * Quản lý duyệt / từ chối đăng ký phát biểu.
     */
    public function updateStatus(SpeechRequest $speechRequest, array $validated): SpeechRequest
    {
        $speechRequest->update($validated);

        return $speechRequest->load(['participant.user', 'agenda']);
    }

    public function destroy(SpeechRequest $speechRequest): void
    {
        $speechRequest->delete();
    }
}
