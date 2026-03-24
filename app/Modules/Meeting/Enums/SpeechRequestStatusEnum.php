<?php

namespace App\Modules\Meeting\Enums;

enum SpeechRequestStatusEnum: string
{
    case Pending = 'pending';    // Chờ duyệt
    case Approved = 'approved';  // Đã duyệt
    case Rejected = 'rejected';  // Từ chối

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
            self::Pending => 'Chờ duyệt',
            self::Approved => 'Đã duyệt',
            self::Rejected => 'Từ chối',
        };
    }
}
