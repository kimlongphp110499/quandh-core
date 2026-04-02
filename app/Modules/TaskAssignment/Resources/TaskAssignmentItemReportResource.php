<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAssignmentItemReportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_assignment_item_id' => $this->task_assignment_item_id,
            'item' => $this->whenLoaded('item', fn () => new TaskAssignmentItemResource($this->item)),
            'reporter_user_id' => $this->reporter_user_id,
            'reporter_name' => $this->reporter?->name ?? 'N/A',
            'completed_at' => $this->completed_at?->format('d/m/Y H:i:s'),
            'report_document_number' => $this->report_document_number,
            'report_document_excerpt' => $this->report_document_excerpt,
            'report_document_content' => $this->report_document_content,
            'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($att) => [
                'id' => $att->id,
                'file_name' => $att->file_name,
                'sort_order' => $att->sort_order,
                'url' => $att->media?->getFullUrl() ?? null,
                'mime_type' => $att->media?->mime_type ?? null,
                'size' => $att->media?->size ?? null,
            ])),
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }
}
