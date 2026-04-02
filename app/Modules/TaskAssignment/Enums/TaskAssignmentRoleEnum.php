<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskAssignmentRoleEnum: string
{
    case Main = 'main';
    case Cooperate = 'cooperate';

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
            self::Main => 'Phòng ban chính',
            self::Cooperate => 'Phòng ban phối hợp',
        };
    }
}
