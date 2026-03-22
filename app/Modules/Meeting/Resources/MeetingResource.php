<?php

namespace App\Modules\Meeting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeetingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'start_at' => $this->start_at?->format('d/m/Y H:i'),
            'end_at' => $this->end_at?->format('d/m/Y H:i'),
            'status' => $this->status,
            'agendas' => $this->whenLoaded('agendas', fn () => AgendaResource::collection($this->agendas)),
            'participants' => $this->whenLoaded('participantRecords', fn () => ParticipantResource::collection($this->participantRecords)),
            'documents' => $this->whenLoaded('documents', fn () => MeetingDocumentResource::collection($this->documents)),
            'conclusions' => $this->whenLoaded('conclusions', fn () => ConclusionResource::collection($this->conclusions)),
            'created_by' => $this->creator?->name ?? 'N/A',
            'updated_by' => $this->editor?->name ?? 'N/A',
            'created_at' => $this->created_at?->format('d/m/Y H:i:s'),
            'updated_at' => $this->updated_at?->format('d/m/Y H:i:s'),
        ];
    }
}
