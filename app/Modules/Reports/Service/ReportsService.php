<?php

namespace App\Modules\Reports\Service;

use App\Enums\OrdersStatusEnum;
use App\Models\CustomersModel;
use App\Models\OrderModel;
use App\Models\ProductModel;

class ReportsService
{
    public function generateRevenueReport(): array
    {
        return [
            'revenue_by_product'           => $this->getRevenueByProduct(),
            'top_5_customers'              => $this->getTopSpendingCustomers(),
            'cancellation_rate_this_month' => $this->getMonthlyCancellationRate(),
            'sales_volume_by_product'      => $this->getSalesVolumeByProduct(), // Đổi tên từ text() cho rõ nghĩa
        ];
    }

    private function getRevenueByProduct()
    {
        // GIẢI PHÁP: Chuyển Subquery thành JOIN đơn giản để Database tối ưu hóa index chéo.
        return ProductModel::query()
            ->join('order_items', 'products.id', '=', 'order_items.product_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrdersStatusEnum::COMPLETED->value)
            ->select(
                'products.id', 
                'products.name', 
                'products.stock_quantity'
            )
            ->selectRaw('SUM(order_items.quantity * order_items.price) as total_revenue')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->groupBy('products.id', 'products.name', 'products.stock_quantity')
            ->orderByDesc('total_revenue')
            ->get();
    }

    private function getTopSpendingCustomers()
    {
        // GIẢI PHÁP: Loại bỏ hoàn toàn việc truy vấn 2 bước và xử lý Map dữ liệu thủ công trên RAM (PHP).
        // Database sẽ chịu trách nhiệm tính tổng tiền và trả về đúng thực thể Customer mong muốn.
        return CustomersModel::query()
            ->join('orders', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.status', OrdersStatusEnum::COMPLETED->value)
            ->select('customers.id', 'customers.name', 'customers.email')
            ->selectRaw('SUM(orders.total_amount) as total_spent')
            ->groupBy('customers.id', 'customers.name', 'customers.email')
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();
    }

    private function getMonthlyCancellationRate()
    {
        // Giữ nguyên logic quét 1 lần tối ưu từ trước, đồng bộ lại định dạng viết code.
        $stats = OrderModel::query()
            ->where('created_at', '>=', now()->startOfMonth())
            ->selectRaw('
                COUNT(id) as total_orders,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled_orders
            ', [OrdersStatusEnum::CANCELLED->value])
            ->first();

        if (!$stats || $stats->total_orders == 0) {
            return 0;
        }

        return round(($stats->cancelled_orders / $stats->total_orders) * 100, 2);
    }

    private function getSalesVolumeByProduct()
    {
        // NỀN TẢNG: Loại bỏ hoàn toàn DB::table(), đồng bộ sang Eloquent thông qua mối quan hệ từ OrderModel
        return OrderModel::query()
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->select('order_items.product_id')
            ->selectRaw('SUM(order_items.quantity) as total_sold')
            ->groupBy('order_items.product_id')
            ->get();
    }
}