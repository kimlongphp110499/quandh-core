<?php

namespace App\Modules\Meeting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meeting_id' => $this->meeting_id,
            'name' => $this->name,
            'type' => $this->type,
            'url' => $this->getFirstMediaUrl('file'),
            'file_type' => $this->getFirstMedia('file')?->extension,
            'uploaded_by' => $this->whenLoaded('uploader', fn () => $this->uploader?->name),
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
        ];
    }
}
