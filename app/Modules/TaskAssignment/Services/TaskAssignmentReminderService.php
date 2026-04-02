<?php

namespace App\Modules\TaskAssignment\Services;

use App\Modules\TaskAssignment\Enums\TaskProgressStatusEnum;
use App\Modules\TaskAssignment\Enums\TaskReminderStatusEnum;
use App\Modules\TaskAssignment\Enums\TaskDeadlineTypeEnum;
use App\Modules\TaskAssignment\Models\TaskAssignmentDocument;
use App\Modules\TaskAssignment\Models\TaskAssignmentItem;
use App\Modules\TaskAssignment\Models\TaskAssignmentReminder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TaskAssignmentReminderService
{
    /** Mốc nhắc trước hạn (ngày) */
    protected array $beforeDeadlineDays = [3, 1];

    /** Mốc nhắc sau hạn (ngày) */
    protected array $afterDeadlineDays = [1, 3, 7];

    public function generateRemindersForDocument(TaskAssignmentDocument $document): void
    {
        $document->loadMissing('items');

        foreach ($document->items as $item) {
            if ($item->deadline_type === TaskDeadlineTypeEnum::HasDeadline->value && $item->end_at) {
                $this->generateRemindersForItem($item);
            }
        }
    }

    public function generateRemindersForItem(TaskAssignmentItem $item): void
    {
        if (! $item->end_at) {
            return;
        }

        $item->loadMissing('users');

        $remindDates = [];

        foreach ($this->beforeDeadlineDays as $days) {
            $remindDates[] = Carbon::parse($item->end_at)->subDays($days)->startOfHour();
        }
        // Đúng ngày hạn
        $remindDates[] = Carbon::parse($item->end_at)->startOfHour();

        foreach ($this->afterDeadlineDays as $days) {
            $remindDates[] = Carbon::parse($item->end_at)->addDays($days)->startOfHour();
        }

        foreach ($remindDates as $remindAt) {
            foreach ($item->users as $user) {
                $this->createReminderIfNotExists($item, $remindAt, 'system', null, $user->id);
            }
        }
    }

    public function dispatchPendingReminders(): int
    {
        $count = 0;

        $reminders = TaskAssignmentReminder::where('status', TaskReminderStatusEnum::Pending->value)
            ->where('remind_at', '<=', now())
            ->with(['item', 'recipientUser'])
            ->get();

        foreach ($reminders as $reminder) {
            try {
                $this->sendReminder($reminder);
                $reminder->update([
                    'status' => TaskReminderStatusEnum::Sent->value,
                    'sent_at' => now(),
                ]);
                $count++;
            } catch (\Throwable $e) {
                $reminder->update([
                    'status' => TaskReminderStatusEnum::Failed->value,
                    'error_message' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    protected function sendReminder(TaskAssignmentReminder $reminder): void
    {
        // Implement notification gửi qua channel tương ứng
        // (in-app, email, zalo, sms) - placeholder
    }

    private function createReminderIfNotExists(
        TaskAssignmentItem $item,
        Carbon $remindAt,
        string $channel,
        ?int $deptId,
        ?int $userId,
    ): void {
        $exists = TaskAssignmentReminder::where('task_assignment_item_id', $item->id)
            ->where('remind_at', $remindAt)
            ->where('channel', $channel)
            ->where('recipient_user_id', $userId)
            ->where('status', TaskReminderStatusEnum::Sent->value)
            ->exists();

        if ($exists) {
            return;
        }

        TaskAssignmentReminder::firstOrCreate([
            'task_assignment_item_id' => $item->id,
            'remind_at' => $remindAt,
            'channel' => $channel,
            'recipient_department_id' => $deptId,
            'recipient_user_id' => $userId,
        ], [
            'status' => TaskReminderStatusEnum::Pending->value,
        ]);
    }
}
