<?php

namespace App\Modules\TaskAssignment\Enums;

enum TaskAssignmentDocumentStatusEnum: string
{
    case Draft = 'draft';
    case Issued = 'issued';

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
            self::Draft => 'Bản nháp',
            self::Issued => 'Đã ban hành',
        };
    }
}
