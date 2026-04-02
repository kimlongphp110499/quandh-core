<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskAssignmentItemTypeCollection extends ResourceCollection
{
    public $collects = TaskAssignmentItemTypeResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
