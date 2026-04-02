<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskReminderStatusEnum: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

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
            self::Pending => 'Chờ gửi',
            self::Sent => 'Đã gửi',
            self::Failed => 'Gửi thất bại',
        };
    }
}
