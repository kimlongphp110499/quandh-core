<?php

namespace App\Modules\Meeting\Enums;

enum MeetingRoleEnum: string
{
    case Chair = 'chair';         // Chủ trì
    case Secretary = 'secretary'; // Thư ký
    case Delegate = 'delegate';   // Đại biểu

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
            self::Chair => 'Chủ trì',
            self::Secretary => 'Thư ký',
            self::Delegate => 'Đại biểu',
        };
    }
}
