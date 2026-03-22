<?php

namespace App\Modules\Meeting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meeting_id' => $this->meeting_id,
            'user_id' => $this->user_id,
            'user_name' => $this->whenLoaded('user', fn () => $this->user->name),
            'user_email' => $this->whenLoaded('user', fn () => $this->user->email),
            'position' => $this->position,
            'meeting_role' => $this->meeting_role,
            'attendance_status' => $this->attendance_status,
            'checkin_at' => $this->checkin_at?->format('d/m/Y H:i:s'),
            'absence_reason' => $this->absence_reason,
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
        ];
    }
}
