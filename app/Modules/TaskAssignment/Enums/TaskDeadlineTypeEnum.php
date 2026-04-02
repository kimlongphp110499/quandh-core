<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskDeadlineTypeEnum: string
{
    case HasDeadline = 'has_deadline';
    case NoDeadline = 'no_deadline';

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
            self::HasDeadline => 'Có thời hạn',
            self::NoDeadline => 'Không có thời hạn',
        };
    }
}
