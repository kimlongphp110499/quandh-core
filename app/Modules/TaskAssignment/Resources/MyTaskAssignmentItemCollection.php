<?php

namespace App\Modules\TaskAssignment\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class MyTaskAssignmentItemCollection extends ResourceCollection
{
    public $collects = MyTaskAssignmentItemResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
