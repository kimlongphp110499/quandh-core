<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskAssignmentUserStatusEnum: string
{
    case Assigned = 'assigned';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Done = 'done';

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
            self::Assigned => 'Đã giao',
            self::Accepted => 'Đã nhận',
            self::Rejected => 'Từ chối',
            self::Done => 'Hoàn thành',
        };
    }
}
