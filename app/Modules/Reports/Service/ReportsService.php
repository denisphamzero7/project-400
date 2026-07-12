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
        return ProductModel::query()
            ->select('products.id', 'products.name', 'products.stock_quantity')
            // Cột Tổng doanh thu
            ->selectRaw('COALESCE((
                SELECT SUM(oi.quantity * oi.price)
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                WHERE oi.product_id = products.id
                  AND o.status = ?
            ), 0) AS total_revenue', [OrdersStatusEnum::COMPLETED->value])
            // Cột Tổng số lượng đã bán
            ->selectRaw('COALESCE((
                SELECT SUM(oi.quantity)
                FROM order_items oi
                JOIN orders o ON o.id = oi.order_id
                WHERE oi.product_id = products.id
                  AND o.status = ?
            ), 0) AS total_sold', [OrdersStatusEnum::COMPLETED->value])
            ->orderByDesc('total_revenue')
            ->get();

    }

    private function getTopSpendingCustomers()
    {
     // Bước 1: Quét bảng orders để tìm 5 ID xuất sắc nhất
    $topCustomersIds = DB::table('orders')
        ->selectRaw('customer_id, SUM(total_amount) as total_spent')
        ->where('status', OrdersStatusEnum::COMPLETED->value)
        ->groupBy('customer_id')
        ->orderByDesc('total_spent')
        ->limit(5)
        ->get();

    if ($topCustomersIds->isEmpty()) {
        return collect();
    }

    // Bước 2: Chỉ query đúng 5 user đó từ bảng Customers
    $customers = CustomersModel::whereIn('id', $topCustomersIds->pluck('customer_id'))
        ->get(['id', 'name', 'email'])
        ->keyBy('id');

    // Bước 3: Ghép dữ liệu tiền vào object để trả về
    return $topCustomersIds->map(function ($stat) use ($customers) {
        $customer = $customers->get($stat->customer_id);
        if ($customer) {
            $customer->total_spent = $stat->total_spent;
            return $customer;
        }
    })->filter();
    }

    private function getMonthlyCancellationRate()
    {
      $startOfMonth = now()->startOfMonth();

    // Lấy TỔNG ĐƠN và TỔNG ĐƠN HỦY trong CÙNG MỘT lần quét
    $stats = OrderModel::query()
        ->where('created_at', '>=', $startOfMonth)
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
}
