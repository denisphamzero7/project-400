<?php

namespace App\Modules\Orders\Exports;

use App\Models\OrderModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected array $filters = []
    ) {}

    /**
     * Xuất danh sách đơn hàng.
     */
    public function collection()
    {
        // Khởi tạo query và áp dụng bộ lọc từ scopeFilter
        $orders = OrderModel::query()
            ->filter($this->filters)
            ->get();

        // Map dữ liệu khớp với các cột thực tế của OrderModel
        return $orders->map(fn ($order) => [
            'id'             => $order->id,
            'customer_id'    => $order->customer_id,
            'total_amount'   => $order->total_amount,
            'status'         => $order->status?->value ?? $order->status ?? 'N/A', // Bắt value từ OrdersStatusEnum
            'created_at'     => $order->created_at?->format('d/m/Y H:i:s'),
            'updated_at'     => $order->updated_at?->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Khai báo dòng tiêu đề (Headings) cho file Excel
     */
    public function headings(): array
    {
        return [
            'ID',
            'Mã khách hàng',
            'Tổng tiền (VNĐ)',
            'Trạng thái',
            'Ngày tạo',
            'Ngày cập nhật',
        ];
    }
}
