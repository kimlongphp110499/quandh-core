<?php

namespace App\Modules\Meeting\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class SpeechRequestCollection extends ResourceCollection
{
    public $collects = SpeechRequestResource::class;

    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
