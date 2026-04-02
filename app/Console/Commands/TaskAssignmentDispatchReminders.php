<?php

namespace App\Console\Commands;

use App\Modules\TaskAssignment\Services\TaskAssignmentReminderService;
use Illuminate\Console\Command;

class TaskAssignmentDispatchReminders extends Command
{
    protected $signature = 'task-assignment:dispatch-reminders';

    protected $description = 'Gửi nhắc việc cho các công việc sắp đến hạn, đến hạn, quá hạn';

    public function handle(TaskAssignmentReminderService $reminderService): int
    {
        $this->info('Đang xử lý nhắc việc...');

        $count = $reminderService->dispatchPendingReminders();

        $this->info("Đã gửi {$count} nhắc việc thành công.");

        return Command::SUCCESS;
    }
}
