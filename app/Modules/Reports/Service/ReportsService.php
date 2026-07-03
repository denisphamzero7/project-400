<?php

namespace App\Modules\Reports\Service;

use App\Enums\OrdersStatusEnum;
use App\Models\CustomersModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use Illuminate\Support\Facades\DB;

class ReportsService
{
    public function generateRevenueReport(): array
    {
        $revenueByProduct = $this->getRevenueByProduct();
        $topCustomers = $this->getTopSpendingCustomers();
        $cancellationRate = $this->getMonthlyCancellationRate();

        return [
            'revenue_by_product' => $revenueByProduct,
            'top_5_customers' => $topCustomers,
            'cancellation_rate_this_month' => $cancellationRate,
        ];
    }

    private function getRevenueByProduct()
    {
        // Yêu cầu: Viết một câu SQL thuần (Raw Query) hoặc Advanced Eloquent kết hợp (Join)
        // Báo cáo trả về: Doanh thu theo từng sản phẩm
        return ProductModel::query()
            ->select('products.id', 'products.name')
            ->withSum(['orderItems' => function ($query) {
                // Chỉ tính doanh thu từ các đơn hàng đã hoàn thành
                $query->whereHas('order', fn($q) => $q->where('status', OrdersStatusEnum::COMPLETED));
            }], DB::raw('quantity * price'))
            ->orderByDesc('order_items_sum_quantity_price')
            ->get();
    }

    private function getTopSpendingCustomers()
    {
        // Yêu cầu: top 5 khách hàng chi tiêu nhiều nhất
        return CustomersModel::query()
            ->select('customers.id', 'customers.name', 'customers.email')
            ->withSum(['orders' => fn($q) => $q->where('status', OrdersStatusEnum::COMPLETED)], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->limit(5)
            ->get();
    }

    private function getMonthlyCancellationRate()
    {
        // Yêu cầu: tỷ lệ đơn hàng bị hủy trong tháng
        $startOfMonth = now()->startOfMonth();

        $totalOrdersThisMonth = OrderModel::query()
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        if ($totalOrdersThisMonth === 0) {
            return 0;
        }

        $cancelledOrdersThisMonth = OrderModel::query()
            ->where('status', OrdersStatusEnum::CANCELLED)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        return round(($cancelledOrdersThisMonth / $totalOrdersThisMonth) * 100, 2);
    }
}
