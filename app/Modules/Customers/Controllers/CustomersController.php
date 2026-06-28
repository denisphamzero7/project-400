<?php

namespace App\Modules\Customers\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CustomersModel;

use App\Modules\Customers\Controllers\FilterRequest as ControllersFilterRequest;
use App\Modules\Customers\Service\CustomersService;

use App\Modules\Customers\Requests\StoreCustomersRequest;
use App\Modules\Customers\Requests\UpdateCustomersRequest;
use App\Modules\Customers\Requests\BulkUpdateStatusCustomersRequest;
use App\Modules\Customers\Requests\BulkDestroyCustomersRequest; // Thêm Request cho xóa hàng loạt
use App\Modules\Customers\Requests\ImportCustomersRequest;      // Thêm Request cho import

use App\Traits\RespondsWithJson;

// Kéo Resource và Collection vào
use App\Modules\Customers\Resources\CustomerCollection;
use App\Modules\Customers\Resources\CustomersResource;

class CustomersController extends Controller
{
    use RespondsWithJson;

    public function __construct(private CustomersService $customerService) {}

    /**
     * Danh sách khách hàng (Có phân trang và bộ lọc)
     */
    public function index(ControllersFilterRequest $request)
    {
        $customers = $this->customerService->index($request->all(), (int) ($request->limit ?? 10));

        return $this->successCollection(new CustomerCollection($customers));
    }

    /**
     * Thống kê khách hàng
     */
    public function stats(ControllersFilterRequest $request)
    {
        return $this->success($this->customerService->stats($request->all()));
    }

    /**
     * Chi tiết 1 khách hàng
     */
    public function show(CustomersModel $customer)
    {
        $customer = $this->customerService->show($customer);

        return $this->successResource(new CustomersResource($customer));
    }

    /**
     * Tạo khách hàng mới
     */
    public function store(StoreCustomersRequest $request)
    {
        $data = $request->validated();
        try {
            $customer = $this->customerService->store($data);

            return $this->successResource(new CustomersResource($customer), 'Khách hàng đã được tạo thành công!', 201);
        } catch (\Throwable $th) {
            return $this->error('Tạo khách hàng thất bại!', 500, null, $th->getMessage());
        }
    }

    /**
     * Cập nhật thông tin khách hàng
     */
    public function update(UpdateCustomersRequest $request, CustomersModel $customer)
    {
        $result = $this->customerService->update($customer, $request->validated());

        if (!$result['ok']) {
            return $this->error($result['message'], $result['code'], null, $result['error_code']);
        }

        return $this->successResource(new CustomersResource($result['customer']), 'Cập nhật thông tin thành công!');
    }

    /**
     * Xóa 1 khách hàng
     */
    public function destroy(CustomersModel $customer)
    {
        $this->customerService->destroy($customer);

        return $this->success(null, 'Khách hàng đã được xóa!');
    }

    /**
     * Xóa hàng loạt khách hàng
     */
    public function bulkDestroy(BulkDestroyCustomersRequest $request)
    {
        $this->customerService->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các khách hàng được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt
     */
    public function bulkUpdateStatus(BulkUpdateStatusCustomersRequest $request)
    {
        $this->customerService->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái hàng loạt thành công!');
    }

    /**
     * Xuất danh sách khách hàng (Excel)
     */
    public function export(ControllersFilterRequest $request)
    {
        // Trả thẳng file BinaryFileResponse từ Service về Client
        return $this->customerService->export($request->all());
    }

    /**
     * Nhập danh sách khách hàng từ file Excel
     */
    public function import(ImportCustomersRequest $request)
    {
        $this->customerService->import($request->file('file'));

        return $this->success(null, 'Import dữ liệu khách hàng thành công.');
    }
}
