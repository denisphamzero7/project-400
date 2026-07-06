<?php

namespace App\Modules\Orders\Controllers;

use App\Http\Controllers\Controller;
use App\Models\OrderModel;
use App\Modules\Orders\Requests\FilterRequest;
use App\Modules\Orders\Service\OrdersService;

use App\Modules\Orders\Requests\StoreOrdersRequest;
use App\Modules\Orders\Requests\UpdateOrdersRequest;
use App\Modules\Orders\Requests\BulkUpdateStatusOrdersRequest;
use App\Modules\Orders\Requests\BulkDestroyOrdersRequest; // Thêm Request cho xóa hàng loạt
use App\Modules\Orders\Requests\ImportOrdersRequest;      // Thêm Request cho import
use App\Modules\Orders\Resources\OrdersCollection;
use App\Traits\RespondsWithJson;

// Kéo Resource và Collection vào

use App\Modules\Orders\Resources\OrdersResource;

class OrdersController extends Controller
{
    use RespondsWithJson;

    public function __construct(private OrdersService $OrdersService) {}

    /**
     * Danh sách đơn hàng (Có phân trang và bộ lọc)
     */
    public function index(FilterRequest $request)
    {
        $orders = $this->OrdersService->index($request->all(), (int) ($request->limit ?? 10));

        return $this->successCollection(new OrdersCollection($orders));
    }

    /**
     * Thống kê đơn hàng
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->OrdersService->stats($request->all()));
    }

    /**
     * Chi tiết 1 đơn hàng
     */
    public function show(OrderModel $order)
    {
        $order = $this->OrdersService->show($order);

        return $this->successResource(new OrdersResource($order));
    }

    /**
     * Tạo đơn hàng mới
     */
    public function store(StoreOrdersRequest $request)
    {
        $data = $request->validated();
        try {
            $order = $this->OrdersService->store($data);

            return $this->successResource(new OrdersResource($order), 'Đơn hàng đã được tạo thành công!', 201);
        } catch (\Throwable $th) {
            return $this->error('Tạo đơn hàng thất bại!', 500, null, $th->getMessage());
        }
    }

    /**
     * Cập nhật thông tin đơn hàng
     */
    public function update(UpdateOrdersRequest $request,OrderModel $order)
    {
        $result = $this->OrdersService->update($order, $request->validated());

        if (!$result['ok']) {
            return $this->error($result['message'], $result['code'], null, $result['error_code']);
        }

        return $this->successResource(new OrdersResource($result['order']), 'Cập nhật thông tin thành công!');
    }

    /**
     * Xóa 1 đơn hàng
     */
    public function destroy(OrderModel $order)
    {
        $this->OrdersService->destroy($order);

        return $this->success(null, 'Đơn hàng đã được xóa!');
    }

    /**
     * Xóa hàng loạt đơn hàng
     */
    public function bulkDestroy(BulkDestroyOrdersRequest $request)
    {
        $this->OrdersService->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các đơn hàng được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt
     */
    public function bulkUpdateStatus(BulkUpdateStatusOrdersRequest $request)
    {
        $this->OrdersService->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái hàng loạt thành công!');
    }

    /**
     * Xuất danh sách đơn hàng (Excel)
     */
    public function export(FilterRequest $request)
    {
        //Trả thẳng file BinaryFileResponse từ Service về Client
        return $this->OrdersService->export($request->all());
    }

    /**
     * Nhập danh sách đơn hàng từ file Excel
     */
    public function import(ImportOrdersRequest $request)
    {
        $this->OrdersService->import($request->file('file'));

        return $this->success(null, 'Import dữ liệu đơn hàng thành công.');
    }

    /**
     * Tải về file PDF của một đơn hàng.
     */
//     public function downloadPdf(OrderModel $order)
//     {
//         return $this->OrdersService->downloadPdf($order);
//     }
}
