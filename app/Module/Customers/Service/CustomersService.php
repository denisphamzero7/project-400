<?php

namespace App\Modules\Customers\Service;

use App\Models\CustomerModel;
use App\Module\Customers\Exports\CustomersExport;
use App\Module\Customers\Imports\CustomersImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Giả định bạn sẽ tạo một Event tương tự như JobActionEvent cho Customer
// use App\Modules\Customers\Events\CustomerActionEvent;

class CustomerService
{
    public function __construct()
    {
    }

    /**
     * Thống kê số lượng khách hàng.
     */
    public function stats(array $filters): array
    {
        $base = CustomerModel::filter($filters);

        // Tùy chỉnh lại các status đếm theo CustomersStatusEnum của bạn
        return [
            'total' => (clone $base)->count(),
            // 'active' => (clone $base)->where('status', 'active')->count(),
            // 'inactive' => (clone $base)->where('status', 'inactive')->count(),
        ];
    }

    /**
     * Lấy danh sách khách hàng có phân trang.
     */
    public function index(array $filters, int $limit)
    {
        // Không dùng organization_id vì CustomerModel không có trường này
        return CustomerModel::filter($filters)
            ->paginate($limit);
    }

    /**
     * Lấy chi tiết khách hàng.
     */
    public function show(CustomerModel $customer): CustomerModel
    {
        // Nếu muốn load kèm danh sách đơn hàng thì mở comment dòng dưới
        // return $customer->load(['orders']);
        return $customer;
    }

    /**
     * Thêm mới khách hàng.
     */
    public function store(array $data): CustomerModel
    {
        $customer = DB::transaction(function () use ($data) {
            return CustomerModel::create($data);
        });

        // Tùy chọn: Bắn realtime nếu cần
        // broadcast(new CustomerActionEvent('customer-created', $customer->toArray()));

        return $customer;
    }

    /**
     * Cập nhật khách hàng.
     */
    public function update(CustomerModel $customer, array $validated): array
    {
        try {
            $updatedCustomer = DB::transaction(function () use ($customer, $validated) {
                $customer->update($validated);
                return $customer;
            });

            // broadcast(new CustomerActionEvent('customer-updated', $updatedCustomer->toArray()));

            return [
                'ok' => true,
                'customer' => $updatedCustomer
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
     * Xóa khách hàng.
     */
    public function destroy(CustomerModel $customer): void
    {
        $id = $customer->id;
        $customer->delete($id);

        // broadcast(new CustomerActionEvent('customer-deleted', $customer->toArray()));
    }

    /**
     * Xóa hàng loạt khách hàng.
     */
    public function bulkDestroy(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            CustomerModel::whereIn('id', $ids)->delete();
        });

        // broadcast(new CustomerActionEvent('customer-bulk-deleted', ['ids' => $ids]));
    }

    /**
     * Cập nhật trạng thái hàng loạt.
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        CustomerModel::whereIn('id', $ids)->update(['status' => $status]);

        // broadcast(new CustomerActionEvent('customer-bulk-status-updated', ['ids' => $ids, 'status' => $status]));
    }

    /**
     * Xuất danh sách khách hàng (Excel).
     */
    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(
            new CustomersExport($filters), // Truyền file Export đã sửa
            'customers.xlsx'
        );
    }

    /**
     * Nhập danh sách khách hàng từ file.
     */
    public function import($file): void
    {
        Excel::import(new CustomersImport(), $file); // Truyền file Import đã sửa
    }
}
