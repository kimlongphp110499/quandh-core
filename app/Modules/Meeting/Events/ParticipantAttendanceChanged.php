<?php

namespace App\Modules\Meeting\Events;

use App\Modules\Meeting\Models\Participant;
use App\Modules\Meeting\Resources\ParticipantResource;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Phát sóng khi trạng thái điểm danh của đại biểu thay đổi.
 *
 * ShouldBroadcastNow: bỏ qua queue, gửi ngay lập tức qua Pusher.
 * Channel: meetings.{meeting_id} — public channel, không cần auth.
 */
class ParticipantAttendanceChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Participant $participant) {}

    /**
     * Kênh phát sóng: public channel theo meeting ID.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('meetings.' . $this->participant->meeting_id);
    }

    /**
     * Tên event được client lắng nghe (dot-prefix trong Echo: .ParticipantAttendanceChanged).
     */
    public function broadcastAs(): string
    {
        return 'ParticipantAttendanceChanged';
    }

    /**
     * Dữ liệu gửi kèm — dùng ParticipantResource để đảm bảo format nhất quán với REST API.
     */
    public function broadcastWith(): array
    {
        return (new ParticipantResource($this->participant->load('user')))->resolve();
    }
}
