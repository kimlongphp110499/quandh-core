<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskAssignmentItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'task_assignment_document_id' => $this->task_assignment_document_id,
            'document' => $this->whenLoaded('document', fn () => new TaskAssignmentDocumentResource($this->document)),
            'name' => $this->name,
            'description' => $this->description,
            'task_assignment_item_type_id' => $this->task_assignment_item_type_id,
            'item_type' => $this->whenLoaded('itemType', fn () => new TaskAssignmentItemTypeResource($this->itemType)),
            'deadline_type' => $this->deadline_type,
            'start_at' => $this->start_at?->format('d/m/Y'),
            'end_at' => $this->end_at?->format('d/m/Y'),
            'processing_status' => $this->processing_status,
            'completion_percent' => (int) $this->completion_percent,
            'priority' => $this->priority,
            'completed_at' => $this->completed_at?->format('d/m/Y H:i:s'),
            'departments' => $this->whenLoaded('departments', fn () => $this->departments->map(fn ($dept) => [
                'id' => $dept->id,
                'code' => $dept->code,
                'name' => $dept->name,
                'role' => $dept->pivot->role,
            ])),
            'users' => $this->whenLoaded('users', fn () => $this->users->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'department_id' => $user->pivot->department_id,
                'assignment_role' => $user->pivot->assignment_role,
                'assignment_status' => $user->pivot->assignment_status,
                'assigned_at' => $user->pivot->assigned_at,
                'accepted_at' => $user->pivot->accepted_at,
                'completed_at' => $user->pivot->completed_at,
                'note' => $user->pivot->note,
            ])),
            'created_by' => $this->creator?->name ?? 'N/A',
            'updated_by' => $this->editor?->name ?? 'N/A',
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }
}
