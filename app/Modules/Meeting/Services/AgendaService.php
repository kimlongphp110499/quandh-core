<?php

namespace App\Modules\Meeting\Services;

use App\Modules\Meeting\Models\Agenda;
use App\Modules\Meeting\Models\Meeting;
use Illuminate\Support\Facades\DB;

class AgendaService
{
    public function index(Meeting $meeting): \Illuminate\Database\Eloquent\Collection
    {
        return $meeting->agendas()->orderBy('order_index')->get();
    }

    public function store(Meeting $meeting, array $validated): Agenda
    {
        if (! isset($validated['order_index'])) {
            $validated['order_index'] = $meeting->agendas()->max('order_index') + 1;
        }

        $validated['meeting_id'] = $meeting->id;

        return Agenda::create($validated);
    }

    public function update(Agenda $agenda, array $validated): Agenda
    {
        $agenda->update($validated);

        return $agenda->fresh();
    }

    public function destroy(Agenda $agenda): void
    {
        $agenda->delete();
    }

    /**
     * Điều hướng cuộc họp đến mục agenda (đánh dấu is_current).
     */
    public function setCurrent(Agenda $agenda): Agenda
    {
        DB::transaction(function () use ($agenda) {
            Agenda::where('meeting_id', $agenda->meeting_id)->update(['is_current' => false]);
            $agenda->update(['is_current' => true]);
        });

        return $agenda->fresh();
    }

    /**
     * Sắp xếp lại thứ tự agenda.
     */
    public function reorder(Meeting $meeting, array $orders): void
    {
        DB::transaction(function () use ($meeting, $orders) {
            foreach ($orders as $item) {
                Agenda::where('id', $item['id'])
                    ->where('meeting_id', $meeting->id)
                    ->update(['order_index' => $item['order_index']]);
            }
        });
    }
}
