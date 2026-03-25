<?php

namespace App\Modules\Meeting\Services;

use App\Modules\Meeting\Models\Conclusion;
use App\Modules\Meeting\Models\Meeting;

class ConclusionService
{
    public function index(Meeting $meeting): \Illuminate\Database\Eloquent\Collection
    {
        return $meeting->conclusions()->with(['agenda', 'creator', 'editor'])->get();
    }

    public function store(Meeting $meeting, array $validated): Conclusion
    {
        $validated['meeting_id'] = $meeting->id;

        return Conclusion::create($validated)->load(['agenda', 'creator', 'editor']);
    }

    public function update(Conclusion $conclusion, array $validated): Conclusion
    {
        $conclusion->update($validated);

        return $conclusion->load(['agenda', 'creator', 'editor']);
    }

    public function destroy(Conclusion $conclusion): void
    {
        $conclusion->delete();
    }
}
