<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAssignmentDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'summary' => $this->summary,
            'issue_date' => $this->issue_date?->format('d/m/Y'),
            'task_assignment_type_id' => $this->task_assignment_type_id,
            'type' => $this->whenLoaded('type', fn () => new TaskAssignmentTypeResource($this->type)),
            'status' => $this->status,
            'issued_at' => $this->issued_at?->format('d/m/Y H:i:s'),
            'issued_by' => $this->issuer?->name ?? null,
            'attachments' => $this->whenLoaded('attachments', fn () => TaskAssignmentDocumentAttachmentResource::collection($this->attachments)),
            'items_count' => $this->whenLoaded('items', fn () => $this->items->count()),
            'created_by' => $this->creator?->name ?? 'N/A',
            'updated_by' => $this->editor?->name ?? 'N/A',
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }
}
