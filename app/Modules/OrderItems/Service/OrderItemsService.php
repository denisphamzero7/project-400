<?php

namespace App\Modules\OrderItems\Service;

use App\Models\ProductModel;
use App\Models\OrderItemModel;
use App\Modules\OrderItems\Exports\OrderItemsExport;
use App\Modules\OrderItems\Imports\OrderItemsImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderItemsService
{
    public function __construct()
    {
    }

    /**
     * Thống kê số lượng chi tiết đơn hàng.
     */
    public function stats(array $filters): array
    {
        $base = OrderItemModel::filter($filters);

        return [
            'total' => (clone $base)->count(),
        ];
    }

    /**
     * Lấy danh sách chi tiết đơn hàng có phân trang.
     * Đã thêm with('product') để hiển thị được product_name trong Resource
     */
    public function index(array $filters, int $limit)
    {
        return OrderItemModel::with(['product']) // Eager load mối quan hệ sản phẩm
            ->filter($filters)
            ->paginate($limit);
    }

    /**
     * Lấy chi tiết một dòng item.
     * Đã sửa từ 'items' thành 'product' và 'order' vì OrderItemModel không có quan hệ 'items'
     */
    public function show(OrderItemModel $orderItem): OrderItemModel
    {
        return $orderItem->load(['order', 'product']);
    }

    /**
     * Thêm mới chi tiết đơn hàng.
     */
    public function store(array $data): OrderItemModel
    {
        $orderItem = DB::transaction(function () use ($data) {
            // Lấy thông tin sản phẩm để có giá chính xác
            $product = ProductModel::findOrFail($data['product_id']);

            // Bổ sung giá vào dữ liệu trước khi tạo
            $dataToCreate = array_merge($data, [
                'price' => $product->price,
            ]);

            return OrderItemModel::create($dataToCreate);
        });

        return $orderItem;
    }

    /**
     * Cập nhật chi tiết đơn hàng.
     */
    public function update(OrderItemModel $orderItem, array $validated): array
    {
        try {
            $updatedOrderItem = DB::transaction(function () use ($orderItem, $validated) {
                // Nếu product_id được gửi lên, tức là người dùng muốn đổi sản phẩm
                if (isset($validated['product_id'])) {
                    // Lấy giá của sản phẩm mới
                    $product = ProductModel::findOrFail($validated['product_id']);
                    // Ghi đè/thêm giá mới vào mảng dữ liệu cập nhật
                    $validated['price'] = $product->price;
                }

                $orderItem->update($validated);
                return $orderItem;
            });

            return [
                'ok' => true,
                'order_item' => $updatedOrderItem
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
     * Xóa một chi tiết đơn hàng.
     */
    public function destroy(OrderItemModel $orderItem): void
    {
        $orderItem->delete();
    }

    /**
     * Xóa hàng loạt chi tiết đơn hàng.
     */
    public function bulkDestroy(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            OrderItemModel::whereIn('id', $ids)->delete();
        });
    }

    /**
     * Cập nhật trạng thái hàng loạt.
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        OrderItemModel::whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Xuất danh sách chi tiết đơn hàng (Excel).
     */
    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(
            new OrderItemsExport($filters),
            'order_items.xlsx'
        );
    }

    /**
     * Nhập danh sách chi tiết đơn hàng từ file.
     */
    public function import($file): void
    {
        Excel::import(new OrderItemsImport(), $file);
    }
}
