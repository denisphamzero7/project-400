<?php

namespace App\Module\Customers\Imports;

use App\Models\CustomerModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomersImport implements ToModel, WithHeadingRow
{
    public function __construct()
    {
        // Đã xóa bỏ parameter $organizationId vì CustomerModel không dùng trường này
    }

    /**
     * Nhập khách hàng từ file Excel.
     */
    public function model(array $row)
    {
        // Tùy chọn: Bỏ qua (return null) nếu dòng Excel bị trống thông tin quan trọng
        if (empty($row['ten_khach_hang']) && empty($row['name'])) {
            return null;
        }

        return new CustomerModel([
            // Thư viện Maatwebsite/Excel (WithHeadingRow) sẽ tự động chuyển dòng Tiêu đề (Headings)
            // thành chữ thường, bỏ dấu và thay khoảng trắng bằng dấu gạch dưới (_).

            'name'           => $row['ten_khach_hang'] ?? $row['name'] ?? '',
            'email'          => $row['email'] ?? null,
            'loyalty_points' => $row['diem_thanh_vien_loyalty_points'] ?? $row['loyalty_points'] ?? 0,

            // Nếu cột trạng thái trong Excel trống, bạn có thể gán cứng giá trị mặc định của hệ thống
            // Ví dụ: 'active', 'pending' (Tùy thuộc vào enum CustomersStatusEnum của bạn)
            'status'         => $row['trang_thai'] ?? $row['status'] ?? null,
        ]);
    }
}
