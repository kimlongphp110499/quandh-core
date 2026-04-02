<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskAssignmentItemCollection extends ResourceCollection
{
    public $collects = TaskAssignmentItemResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
