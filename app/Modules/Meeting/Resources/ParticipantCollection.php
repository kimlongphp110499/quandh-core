<?php

namespace App\Modules\Meeting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ParticipantCollection extends ResourceCollection
{
    public $collects = ParticipantResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
