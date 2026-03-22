<?php

namespace App\Modules\Meeting\Enums;

enum AttendanceStatusEnum: string
{
    case NotArrived = 'not_arrived'; // Chưa đến
    case Present = 'present';        // Đã đến
    case Absent = 'absent';          // Vắng

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function rule(): string
    {
        return 'in:' . implode(',', self::values());
    }

    public function label(): string
    {
        return match ($this) {
            self::NotArrived => 'Chưa đến',
            self::Present => 'Đã đến',
            self::Absent => 'Vắng',
        };
    }
}
