<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskAssignmentDocumentCollection extends ResourceCollection
{
    public $collects = TaskAssignmentDocumentResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
