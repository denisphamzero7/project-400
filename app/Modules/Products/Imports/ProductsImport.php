<?php

namespace App\Modules\Products\Imports;

use App\Models\ProductModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel, WithHeadingRow
{
    public function __construct()
    {
    }

    /**
     * Nhập sản phẩm từ file Excel.
     */
    public function model(array $row)
    {
        // Tùy chọn: Bỏ qua (return null) nếu dòng Excel bị trống thông tin bắt buộc (Tên sản phẩm)
        if (empty($row['ten_san_pham']) && empty($row['name'])) {
            return null;
        }

        return new ProductModel([
            // Bắt các cột có khả năng xuất hiện trong file Excel (Tiếng Việt hoặc Tiếng Anh)
            'name'           => $row['ten_san_pham'] ?? $row['name'] ?? '',

            // Ép kiểu về số (float/int) để đảm bảo an toàn dữ liệu, mặc định là 0 nếu trống
            'price'          => isset($row['gia']) ? (float) $row['gia'] : (isset($row['price']) ? (float) $row['price'] : 0),

            'stock_quantity' => isset($row['so_luong_ton_kho']) ? (int) $row['so_luong_ton_kho'] : (isset($row['stock_quantity']) ? (int) $row['stock_quantity'] : 0),

            // Nếu bảng Products của bạn có trường status, có thể để dòng này, nếu không thì xóa đi
            // 'status'         => $row['trang_thai'] ?? $row['status'] ?? 'active',
        ]);
    }
}
