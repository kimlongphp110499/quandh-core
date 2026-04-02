<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskAssignmentDepartmentCollection extends ResourceCollection
{
    public $collects = TaskAssignmentDepartmentResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
