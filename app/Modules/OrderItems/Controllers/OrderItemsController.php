<?php

namespace App\Modules\OrderItems\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderItemModel;
use App\Modules\OrderItems\Requests\FilterRequest;
use App\Modules\OrderItems\Service\OrderItemsService;

use App\Modules\OrderItems\Requests\StoreOrderItemsRequest;
use App\Modules\OrderItems\Requests\UpdateOrderItemsRequest;
use App\Modules\OrderItems\Requests\BulkUpdateStatusOrderItemsRequest;
use App\Modules\OrderItems\Requests\BulkDestroyOrderItemsRequest; // Thêm Request cho xóa hàng loạt
use App\Modules\OrderItems\Requests\ImportOrderItemsRequest;      // Thêm Request cho import
use App\Modules\OrderItems\Resources\OrderItemsCollection;
use App\Traits\RespondsWithJson;

// Kéo Resource và Collection vào

use App\Modules\OrderItems\Resources\OrderItemsResource;

class OrderItemsController extends Controller
{
    use RespondsWithJson;

    public function __construct(private OrderItemsService $OrderItemsService) {}

    /**
     * Danh sách đơn hàng (Có phân trang và bộ lọc)
     */
    public function index(FilterRequest $request)
    {
        $OrderItems = $this->OrderItemsService->index($request->all(), (int) ($request->limit ?? 10));

        return $this->successCollection(new OrderItemsCollection($OrderItems));
    }

    /**
     * Thống kê đơn hàng
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->OrderItemsService->stats($request->all()));
    }

    /**
     * Chi tiết 1 đơn hàng
     */
    public function show(OrderItemModel $orderItem)
    {
        $orderItem = $this->OrderItemsService->show($orderItem);

        return $this->successResource(new OrderItemsResource($orderItem));
    }

    /**
     * Tạo đơn hàng mới
     */
    public function store(StoreOrderItemsRequest $request)
    {
        $data = $request->validated();
        try {
            $orderItem = $this->OrderItemsService->store($data);

            return $this->successResource(new OrderItemsResource($orderItem), 'Chi tiết đơn hàng đã được tạo thành công!', 201);
        } catch (\Throwable $th) {
            return $this->error('Tạo chi tiết đơn hàng thất bại!', 500, null, $th->getMessage());
        }
    }

    /**
     * Cập nhật thông tin đơn hàng
     */
    public function update(UpdateOrderItemsRequest $request, OrderItemModel $orderItem)
    {
        $result = $this->OrderItemsService->update($orderItem, $request->validated());

        if (!$result['ok']) {
            return $this->error($result['message'], $result['code'], null, $result['error_code']);
        }

        return $this->successResource(new OrderItemsResource($result['order_item']), 'Cập nhật thông tin thành công!');
    }

    /**
     * Xóa 1 đơn hàng
     */
    public function destroy(OrderItemModel $orderItem)
    {
        $this->OrderItemsService->destroy($orderItem);

        return $this->success(null, 'Đơn hàng đã được xóa!');
    }

    /**
     * Xóa hàng loạt đơn hàng
     */
    public function bulkDestroy(BulkDestroyOrderItemsRequest $request)
    {
        $this->OrderItemsService->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các đơn hàng được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt
     */
    public function bulkUpdateStatus(BulkUpdateStatusOrderItemsRequest $request)
    {
        $this->OrderItemsService->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái hàng loạt thành công!');
    }

    /**
     * Xuất danh sách đơn hàng (Excel)
     */
    public function export(FilterRequest $request)
    {
        // Trả thẳng file BinaryFileResponse từ Service về Client
        return $this->OrderItemsService->export($request->all());
    }

    /**
     * Nhập danh sách đơn hàng từ file Excel
     */
    public function import(ImportOrderItemsRequest $request)
    {
        $this->OrderItemsService->import($request->file('file'));

        return $this->success(null, 'Import dữ liệu đơn hàng thành công.');
    }
}
