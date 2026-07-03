<?php

namespace App\Enums;

enum OrdersStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';


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
           self::PENDING => 'Đang chờ',
           self::PROCESSING => 'Đang xử lý',
           self::COMPLETED => 'Hoàn thành',
           self::CANCELLED => 'Đã hủy',
           self::EXPIRED => 'Hết hạn',
        };
    }

}

