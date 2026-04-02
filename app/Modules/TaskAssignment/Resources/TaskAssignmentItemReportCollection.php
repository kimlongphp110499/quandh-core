<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class TaskAssignmentItemReportCollection extends ResourceCollection
{
    public $collects = TaskAssignmentItemReportResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
