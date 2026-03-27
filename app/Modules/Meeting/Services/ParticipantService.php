<?php

namespace App\Modules\Meeting\Services;

use App\Modules\Meeting\Events\ParticipantAttendanceChanged;
use App\Modules\Meeting\Exports\ParticipantsExport;
use App\Modules\Meeting\Imports\ParticipantsImport;
use App\Modules\Meeting\Models\Meeting;
use App\Modules\Meeting\Models\Participant;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ParticipantService
{
    public function index(Meeting $meeting): \Illuminate\Database\Eloquent\Collection
    {
        return $meeting->participantRecords()->with('user')->get();
    }

    public function show(Participant $participant): Participant
    {
        return $participant->load('user');
    }

    /**
     * Thêm nhiều đại biểu vào cuộc họp cùng lúc.
     */
    public function store(Meeting $meeting, array $validated): \Illuminate\Database\Eloquent\Collection
    {
        $role = $validated['meeting_role'];
        $position = $validated['position'] ?? null;
        $userIds = $validated['user_ids'];

        DB::transaction(function () use ($meeting, $userIds, $role, $position) {
            foreach ($userIds as $userId) {
                // Bỏ qua nếu đã có trong meeting
                $exists = $meeting->participantRecords()->where('user_id', $userId)->exists();
                if (! $exists) {
                    Participant::create([
                        'meeting_id' => $meeting->id,
                        'user_id' => $userId,
                        'meeting_role' => $role,
                        'position' => $position,
                    ]);
                }
            }
        });

        return $meeting->participantRecords()->with('user')->whereIn('user_id', $userIds)->get();
    }

    public function update(Participant $participant, array $validated): Participant
    {
        $participant->update($validated);

        return $participant->load('user');
    }

    public function destroy(Participant $participant): void
    {
        $participant->delete();
    }

    /**
     * Điểm danh: đại biểu tự xác nhận hoặc quản lý cập nhật trạng thái.
     */
    public function checkin(Participant $participant, string $attendanceStatus, ?string $absenceReason = null): Participant | array
    {
        $data = ['attendance_status' => $attendanceStatus];

        if ($attendanceStatus === 'present') {
            $data['checkin_at'] = now();
        }

        if ($absenceReason) {
            $data['absence_reason'] = $absenceReason;
        }

        $participant->update($data);
        $participant->load('user');

        broadcast(new ParticipantAttendanceChanged($participant));

        return $participant;
    }

    /**
     * Admin cưỡng chế thay đổi attendance_status (không cần kiểm tra conflict).
     */
    public function changeStatus(Participant $participant, string $attendanceStatus, ?string $absenceReason = null): Participant
    {
        $data = ['attendance_status' => $attendanceStatus];

        if ($attendanceStatus === 'present' && ! $participant->checkin_at) {
            $data['checkin_at'] = now();
        }

        if ($absenceReason !== null) {
            $data['absence_reason'] = $absenceReason;
        }

        $participant->update($data);
        $participant->load('user');

        broadcast(new ParticipantAttendanceChanged($participant));

        return $participant;
    }

    public function bulkDestroy(Meeting $meeting, array $ids): void
    {
        Participant::whereIn('id', $ids)
            ->where('meeting_id', $meeting->id)
            ->delete();
    }

    public function bulkUpdateStatus(Meeting $meeting, array $ids, string $attendanceStatus): void
    {
        Participant::whereIn('id', $ids)
            ->where('meeting_id', $meeting->id)
            ->update(['attendance_status' => $attendanceStatus]);
    }

    public function export(Meeting $meeting): BinaryFileResponse
    {
        $filename = 'participants-meeting-' . $meeting->id . '.xlsx';

        return Excel::download(new ParticipantsExport($meeting), $filename);
    }

    public function import(Meeting $meeting, $file): void
    {
        Excel::import(new ParticipantsImport($meeting), $file);
    }
}
