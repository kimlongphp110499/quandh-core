<?php

namespace App\Modules\Meeting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ConclusionCollection extends ResourceCollection
{
    public $collects = ConclusionResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
