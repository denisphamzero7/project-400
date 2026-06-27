<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

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
            self::PENDING => 'Chờ thanh toán',
            self::PAID => 'Đã thanh toán',
            self::CANCELLED => 'Đã hủy',
            self::EXPIRED => 'Đã hết hạn',

        };
    }
}
