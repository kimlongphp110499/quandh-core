<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAssignmentDocumentAttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'media_id' => $this->media_id,
            'file_name' => $this->file_name,
            'sort_order' => (int) $this->sort_order,
            'url' => $this->media?->getFullUrl() ?? null,
            'mime_type' => $this->media?->mime_type ?? null,
            'size' => $this->media?->size ?? null,
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
        ];
    }
}
