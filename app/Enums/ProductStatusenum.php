<?php 
namespace App\Enums;

enum ProductStatusEnum: string
{
    case ACTIVE ='active'; // đang bán
    case DRAFT ='draft'; // Bảng nháp, chưa hiển thị
    case ARCHIVED='archived'; // đã lưu trữ/ ngừng khinh doanh
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
           self::ACTIVE => 'Đang hoạt động',
            self::DRAFT => 'Bản nháp',
            self::ARCHIVED => 'Ngừng kinh doanh',

        };
    }

}