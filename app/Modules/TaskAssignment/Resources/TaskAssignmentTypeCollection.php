<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskAssignmentTypeCollection extends ResourceCollection
{
    public $collects = TaskAssignmentTypeResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
