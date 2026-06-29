<?php

namespace App\Modules\OrderItems\Imports;

use App\Models\OrderItemModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrderItemsImport implements ToModel, WithHeadingRow
{
    public function __construct()
    {
    }

    /**
     * Nhập đơn hàng từ file Excel.
     */
    public function model(array $row)
    {
        // Tùy chọn: Bỏ qua (return null) nếu dòng Excel bị trống thông tin bắt buộc (Mã khách hàng)
        if (empty($row['ma_khach_hang']) && empty($row['customer_id'])) {
            return null;
        }

        return new OrderItemModel([
            // Bắt các cột có khả năng xuất hiện trong file Excel (Tiếng Việt hoặc Tiếng Anh)
            'customer_id'    => $row['ma_khach_hang'] ?? $row['customer_id'] ?? '',
            'total_amount'   => isset($row['tong_tien']) ? (float) $row['tong_tien'] : (isset($row['total_amount']) ? (float) $row['total_amount'] : 0),
            'status'         => $row['trang_thai'] ?? $row['status'] ?? 'pending',
        ]);
    }
}
