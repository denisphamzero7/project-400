<?php

namespace App\Modules\Orders\Service;


use App\Models\OrderModel;

use App\Events\OrderPaid;
use App\Enums\OrdersStatusEnum;
use App\Events\BigOrderPlaced;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use Exception;
use App\Modules\Orders\Exports\OrdersExport;
use App\Modules\Orders\Imports\OrdersImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Orders\Events\OrderActionEvent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrdersService
{
    public function __construct()
    {
    }

    /**
     * Thống kê số lượng đơn hàng.
     */
    public function stats(array $filters): array
    {
        $base = OrderModel::filter($filters);

        // Tùy chỉnh lại các status đếm theo OrdersStatusEnum của bạn
        // Ví dụ: 'pending' => (clone $base)->where('status', OrderStatusEnum::PENDING)->count(),
        // Đảm bảo OrderStatusEnum đã được định nghĩa với các giá trị tương ứng.
        return [
            'total' => (clone $base)->count(),
            // 'pending' => (clone $base)->where('status', 'pending')->count(),
            // 'completed' => (clone $base)->where('status', 'completed')->count(),
        ];
    }

    /**
     * Lấy danh sách đơn hàng có phân trang.
     */
    public function index(array $filters, int $limit)
    {
        // Không dùng organization_id vì OrderModel không có trường này
        return OrderModel::filter($filters)
            ->paginate($limit);
    }

    /**
     * Lấy chi tiết đơn hàng.
     */
    public function show(OrderModel $order): OrderModel
    {
        return $order->load(['items', 'customer']); // Tải mối quan hệ 'items' và 'customer'
    }

    /**
     * Thêm mới đơn hàng.
     */
    public function store(array $data): OrderModel
    {
        $order = DB::transaction(function () use ($data) {
            // Tách riêng data cho order và order items
            $orderData = [
                'customer_id' => $data['customer_id'],
                'status' => $data['status'] ?? 'pending', // Mặc định khi mới tạo
                'total_amount' => 0, // Sẽ được tính và cập nhật lại ngay trong transaction
            ];
            $order = OrderModel::create($orderData);

            $items = $data['items'] ?? [];

            if (!empty($items)) {
                $productIds = array_column($items, 'product_id');
                $products = ProductModel::find($productIds)->keyBy('id');

                if (count($products) !== count($productIds)) {
                    throw new Exception("Một hoặc nhiều sản phẩm không tồn tại.");
                }

                $orderItemsData = [];
                $stockUpdates = [];

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    if (!$product) {
                        throw new Exception("Sản phẩm với ID {$item['product_id']} không tồn tại.");
                    }

                    if ($product->stock_quantity < $item['quantity']) {
                        throw new Exception("Sản phẩm '{$product->name}' không đủ số lượng tồn kho.");
                    }

                    $itemPrice = $product->price;

                    $orderItemsData[] = [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $itemPrice,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Chuẩn bị câu lệnh update stock
                    $stockUpdates[] = "WHEN {$product->id} THEN stock_quantity - {$item['quantity']}";
                }

                // Bulk insert order items
                OrderItemModel::insert($orderItemsData);

                // Bulk update stock quantity
                if (!empty($stockUpdates)) {
                    $ids = implode(',', $productIds);
                    $cases = implode(' ', $stockUpdates);
                    DB::update("UPDATE products SET stock_quantity = CASE id {$cases} END WHERE id IN ({$ids})");
                }
            }

            return $order;
        });

        // Tùy chọn: Bắn realtime nếu cần
        broadcast(new OrderActionEvent('created', $order->toArray()));

        return $order;
    }

    /**
     * Cập nhật đơn hàng.
     */
    public function update(OrderModel $order, array $validated): array
    {
        try {
            $updatedOrder = DB::transaction(function () use ($order, $validated) {
                $order->update($validated);
                return $order;
            });

            // Nếu trạng thái đơn hàng được cập nhật thành "completed" (đã thanh toán)
            if (isset($validated['status']) && $validated['status'] === OrdersStatusEnum::COMPLETED->value) {
                // Kích hoạt event OrderPaid
                event(new OrderPaid($updatedOrder));
            }


            broadcast(new OrderActionEvent('updated', $updatedOrder->toArray())); // Kích hoạt sự kiện cập nhật

            return [
                'ok' => true,
                'order' => $updatedOrder
            ];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage(),
                'code' => 500,
                'error_code' => 'UPDATE_ERROR'
            ];
        }
    }

    /**
     * Xóa đơn hàng.
     */
    public function destroy(OrderModel $order): void
    {
        $id = $order->id;
        $order->delete();

        broadcast(new OrderActionEvent('deleted', ['id' => $id])); // Kích hoạt sự kiện xóa
    }

    /**
     * Xóa hàng loạt đơn hàng.
     */
    public function bulkDestroy(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            OrderModel::whereIn('id', $ids)->delete();
        });
        broadcast(new OrderActionEvent('bulk-deleted', ['ids' => $ids]));
    }

    /**
     * Cập nhật trạng thái hàng loạt.
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        OrderModel::whereIn('id', $ids)->update(['status' => $status]); // Cập nhật trạng thái

        broadcast(new OrderActionEvent('bulk-status-updated', ['ids' => $ids, 'status' => $status]));
    }

    /**
     * Xuất danh sách đơn hàng (Excel).
     */
    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(
            new OrdersExport($filters), // Truyền file Export đã sửa
            'orders.xlsx'
        );
    }

    /**
     * Nhập danh sách đơn hàng từ file.
     */
    public function import($file): void
    {
        Excel::import(new OrdersImport(), $file); // Truyền file Import đã sửa
    }
}
