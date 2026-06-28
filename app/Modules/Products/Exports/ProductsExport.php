<?php

namespace App\Modules\Products\Exports;

use App\Models\ProductModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected array $filters = []
    ) {}

    /**
     * Xuất danh sách sản phẩm.
     */
    public function collection()
    {
        // Khởi tạo query và áp dụng bộ lọc từ scopeFilter
        $products = ProductModel::query()
            ->filter($this->filters)
            ->get();

        // Map dữ liệu khớp với các cột thực tế của ProductModel
        return $products->map(fn ($product) => [
            'id'             => $product->id,
            'name'           => $product->name,
            'price'          => $product->price,
            'stock_quantity' => $product->stock_quantity,
            'status'         => $product->status?->value ?? $product->status ?? 'N/A', // Bắt value từ ProductStatusEnum
            'created_at'     => $product->created_at?->format('d/m/Y H:i:s'),
            'updated_at'     => $product->updated_at?->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Khai báo dòng tiêu đề (Headings) cho file Excel
     */
    public function headings(): array
    {
        return [
            'ID',
            'Tên sản phẩm',
            'Giá bán (VNĐ)',
            'Số lượng tồn kho',
            'Trạng thái',
            'Ngày tạo',
            'Ngày cập nhật',
        ];
    }
}
