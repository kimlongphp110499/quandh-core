<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskAssignmentUserRoleEnum: string
{
    case Main = 'main';
    case Support = 'support';

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
            self::Main => 'Người chủ trì',
            self::Support => 'Người phối hợp',
        };
    }
}
