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
use Barryvdh\DomPDF\Facade\Pdf;
use App\Modules\Orders\Events\OrderActionEvent;
use Illuminate\Support\Facades\Log;
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
            'total'     => (clone $base)->count(),
            'pending'   => (clone $base)->where('status', OrdersStatusEnum::PENDING)->count(),
            'completed' => (clone $base)->where('status', OrdersStatusEnum::COMPLETED)->count(),
            'cancelled' => (clone $base)->where('status', OrdersStatusEnum::CANCELLED)->count(),
            'expired'   => (clone $base)->where('status', OrdersStatusEnum::EXPIRED)->count(),
        ];
    }

    /**
     * Lấy danh sách đơn hàng có phân trang.
     */
    public function index(array $filters, int $limit)
{
    try {
        // Thực hiện query và gán vào biến
        $orders = OrderModel::filter($filters)->paginate($limit);

        // Log danh sách kết quả để xem có gì bên trong
        // Sử dụng toArray() để dễ đọc dữ liệu phân trang trong file log
        Log::info('Danh sách Orders [Index]:', [
            'filters' => $filters,
            'limit' => $limit,
            'data' => $orders->toArray()
        ]);

        return $orders;

    } catch (Exception $e) {
        // Log lại chi tiết lỗi nếu query thất bại
        Log::error('Lỗi khi lấy danh sách Orders: ' . $e->getMessage(), [
            'filters' => $filters,
            'limit' => $limit,
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        // Bạn có thể return một response báo lỗi hoặc throw lỗi tuỳ thuộc vào logic ứng dụng
        // Ví dụ: return response()->json(['message' => 'Có lỗi xảy ra!'], 500);
        throw $e;
    }
}

    /**
     * Lấy chi tiết đơn hàng.
     */
    public function show(OrderModel $order): OrderModel
    {
        return $order->load(['customer', 'items.product']); // Tải mối quan hệ 'items' và 'customer'
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
                $totalAmount = 0; // 1. Khởi tạo biến tính tổng tiền

                foreach ($items as $item) {
                    $product = $products->get($item['product_id']);

                    if (!$product) {
                        throw new Exception("Sản phẩm với ID {$item['product_id']} không tồn tại.");
                    }

                    if ($product->stock_quantity < $item['quantity']) {
                        throw new Exception("Sản phẩm '{$product->name}' không đủ số lượng tồn kho.");
                    }

                    $itemPrice = $product->price;

                    // 2. Cộng dồn vào tổng tiền
                    $totalAmount += $itemPrice * $item['quantity'];

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

                    // 3. Cập nhật tổng tiền cho đơn hàng
                    $order->total_amount = $totalAmount;
                    $order->save();
                }
            }

            // 4. Làm mới model để lấy dữ liệu mới nhất từ DB (bao gồm total_amount)
            $order->refresh();

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

    /**
     * Tạo và trả về file PDF cho một đơn hàng.
     */
    public function downloadPdf(OrderModel $order)
    {
        // Eager load các mối quan hệ cần thiết để tránh query N+1
        $order->load('customer', 'items.product');

        // Tạo PDF từ Blade view
        $pdf = Pdf::loadView('pdfs.order_pdf', ['order' => $order]);

        // Trả về file PDF để trình duyệt tải xuống
        return $pdf->download('hoa-don-'.$order->id.'.pdf');
    }
}
