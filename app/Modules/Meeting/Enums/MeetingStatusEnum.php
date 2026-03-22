<?php

namespace App\Modules\Meeting\Enums;

enum MeetingStatusEnum: string
{
    case Draft = 'draft';
    case Active = 'active';
    case InProgress = 'in_progress';
    case Ended = 'ended';

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
            self::Draft => 'Bản nháp',
            self::Active => 'Đã kích hoạt',
            self::InProgress => 'Đang họp',
            self::Ended => 'Đã kết thúc',
        };
    }
}
