<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskReminderChannelEnum: string
{
    case System = 'system';
    case Email = 'email';
    case Zalo = 'zalo';
    case Sms = 'sms';

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
            self::System => 'Hệ thống',
            self::Email => 'Email',
            self::Zalo => 'Zalo',
            self::Sms => 'SMS',
        };
    }
}
