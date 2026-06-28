<?php

namespace App\Modules\Orders\Service;


use App\Models\OrderModel;

use App\Modules\Orders\Exports\OrdersExport;
use App\Modules\Orders\Imports\OrdersImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Giả định bạn sẽ tạo một Event tương tự như JobActionEvent cho Order
// use App\Moduless\Orders\Events\OrderActionEvent;

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
        return $order->load(['items']); // Tải mối quan hệ 'items' (chi tiết đơn hàng)
        return $order;
    }

    /**
     * Thêm mới đơn hàng.
     */
    public function store(array $data): OrderModel
    {
        $order = DB::transaction(function () use ($data) {
            return OrderModel::create($data);
        });

        // Tùy chọn: Bắn realtime nếu cần (đảm bảo OrderActionEvent đã được tạo và cấu hình)
        // broadcast(new OrderActionEvent('order-created', $order->toArray()));

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

            // broadcast(new OrderActionEvent('order-updated', $updatedOrder->toArray())); // Kích hoạt sự kiện cập nhật

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
        $order->delete($id);

        // broadcast(new OrderActionEvent('order-deleted', $order->toArray())); // Kích hoạt sự kiện xóa
    }

    /**
     * Xóa hàng loạt đơn hàng.
     */
    public function bulkDestroy(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            OrderModel::whereIn('id', $ids)->delete();
        });
        // broadcast(new OrderActionEvent('order-bulk-deleted', ['ids' => $ids])); // Kích hoạt sự kiện xóa hàng loạt
        // broadcast(new OrderActionEvent('order-bulk-deleted', ['ids' => $ids]));
    }

    /**
     * Cập nhật trạng thái hàng loạt.
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        OrderModel::whereIn('id', $ids)->update(['status' => $status]); // Cập nhật trạng thái

        // broadcast(new OrderActionEvent('order-bulk-status-updated', ['ids' => $ids, 'status' => $status]));
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
