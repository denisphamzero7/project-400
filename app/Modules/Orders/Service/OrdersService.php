<?php

namespace App\Modules\Orders\Service;

use App\Models\OrderModel;
use App\Enums\OrdersStatusEnum;
use App\Models\OrderItemModel;
use App\Models\ProductModel;
use App\Modules\Orders\Exports\OrdersExport;
use App\Modules\Orders\Imports\OrdersImport;
use App\Modules\Orders\Events\OrderActionEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use RuntimeException;
use Exception;

class OrdersService
{
    /**
     * Thống kê số lượng đơn hàng.
     * SỰ THẬT: Gom 5 query thành 1 query duy nhất bằng Conditional Aggregation.
     */
    public function stats(array $filters): array
    {
        $stats = OrderModel::filter($filters)
            ->reorder() // Xóa bỏ ORDER BY (nếu có từ filter) để tránh lỗi SQLSTATE 1140
            ->selectRaw('
                COUNT(id) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as expired
            ', [
                OrdersStatusEnum::PENDING->value,
                OrdersStatusEnum::COMPLETED->value,
                OrdersStatusEnum::CANCELLED->value,
                OrdersStatusEnum::EXPIRED->value
            ])
            ->first();

        // Ép kiểu về int vì SUM() trong SQL thường trả về chuỗi (string)
        return [
            'total'     => (int) ($stats->total ?? 0),
            'pending'   => (int) ($stats->pending ?? 0),
            'completed' => (int) ($stats->completed ?? 0),
            'cancelled' => (int) ($stats->cancelled ?? 0),
            'expired'   => (int) ($stats->expired ?? 0),
        ];
    }

    /**
     * Lấy danh sách đơn hàng có phân trang.
     */
    public function index(array $filters, int $limit)
    {
        // Ghi log là thói quen tốt, tiếp tục duy trì để trace lỗi
        try {
            return OrderModel::with(['customer', 'items.product'])->filter($filters)->paginate($limit);
        } catch (Exception $e) {
            Log::error('Lỗi khi lấy danh sách Orders: ' . $e->getMessage(), [
                'filters' => $filters,
                'limit'   => $limit,
            ]);
            throw new RuntimeException('Lỗi hệ thống khi tải danh sách đơn hàng.');
        }
    }

    public function show(OrderModel $order): OrderModel
    {
        return $order->load(['customer', 'items.product']);
    }

    /**
     * Thêm mới đơn hàng.
     * SỰ THẬT: Ở đây BẮT BUỘC dùng DB::transaction vì có nhiều thao tác ghi liên tiếp.
     */
    public function store(array $data): OrderModel
    {
        $order = DB::transaction(function () use ($data) {
            $order = OrderModel::create([
                'customer_id'  => $data['customer_id'],
                'status'       => $data['status'] ?? OrdersStatusEnum::PENDING->value,
                'total_amount' => 0, 
            ]);

            $items = $data['items'] ?? [];

            if (!empty($items)) {
                $productIds = array_column($items, 'product_id');
                $products = ProductModel::whereIn('id', $productIds)->get()->keyBy('id');

                if ($products->count() !== count($productIds)) {
                    throw new RuntimeException("Một hoặc nhiều sản phẩm không tồn tại trong hệ thống.");
                }

                $orderItemsData = [];
                $totalAmount = 0;

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    if ($product->stock_quantity < $item['quantity']) {
                        throw new RuntimeException("Sản phẩm '{$product->name}' không đủ số lượng tồn kho.");
                    }

                    // 1. Tính toán
                    $itemPrice = $product->price;
                    $totalAmount += $itemPrice * $item['quantity'];

                    // 2. Chuẩn bị data cho Order Items
                    $orderItemsData[] = [
                        'order_id'   => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $item['quantity'],
                        'price'      => $itemPrice,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // 3. FIX BUG BẢO MẬT: Cập nhật tồn kho an toàn tuyệt đối qua ORM thay vì nối chuỗi SQL
                    $product->stock_quantity -= $item['quantity'];
                    $product->save(); 
                }

                // Chèn hàng loạt Order Items (vẫn rất nhanh)
                OrderItemModel::insert($orderItemsData);

                // Cập nhật tổng tiền đơn hàng
                $order->update(['total_amount' => $totalAmount]);
            }

            return $order;
        });

        broadcast(new OrderActionEvent('created', $order->toArray()));

        return $order;
    }

    /**
     * Cập nhật đơn hàng.
     * SỰ THẬT: Khai tử logic trả về mảng. Chỉ ném Exception nếu lỗi.
     */
    public function update(OrderModel $order, array $validated): OrderModel
    {
        if (!$order->update($validated)) {
            throw new RuntimeException('Không thể cập nhật thông tin đơn hàng.');
        }

        broadcast(new OrderActionEvent('updated', $order->toArray()));

        return $order;
    }

    public function destroy(OrderModel $order): void
    {
        $id = $order->id;
        
        if (!$order->delete()) {
            throw new RuntimeException('Không thể xóa đơn hàng này.');
        }

        broadcast(new OrderActionEvent('deleted', ['id' => $id]));
    }

    public function bulkDestroy(array $ids): void
    {
        OrderModel::whereIn('id', $ids)->delete();
        broadcast(new OrderActionEvent('bulk-deleted', ['ids' => $ids]));
    }

    public function bulkUpdateStatus(array $ids, string $status): void
    {
        OrderModel::whereIn('id', $ids)->update(['status' => $status]);
        broadcast(new OrderActionEvent('bulk-status-updated', ['ids' => $ids, 'status' => $status]));
    }

    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(new OrdersExport($filters), 'orders.xlsx');
    }

    public function import($file): void
    {
        Excel::import(new OrdersImport(), $file);
    }
}