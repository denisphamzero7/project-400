<?php

namespace App\Enums;

enum CustomersStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

     /** Danh sách giá trị để validate. */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /** Rule validation: in:draft,published,archived */
    public static function rule(): string
    {
        return 'in:'.implode(',', self::values());
    }
     /** Nhãn tiếng Việt. */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Hoạt động',
            self::INACTIVE => 'Không hoạt động',

        };
    }
}
