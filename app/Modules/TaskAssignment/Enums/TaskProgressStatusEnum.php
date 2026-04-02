<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskProgressStatusEnum: string
{
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Overdue = 'overdue';
    case Paused = 'paused';
    case Cancelled = 'cancelled';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }

    public function label(): string
    {
        return match ($this) {
            self::Todo => 'Chưa bắt đầu',
            self::InProgress => 'Đang thực hiện',
            self::Done => 'Hoàn thành',
            self::Overdue => 'Quá hạn',
            self::Paused => 'Tạm dừng',
            self::Cancelled => 'Đã hủy',
        };
    }
}
