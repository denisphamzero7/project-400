<?php

namespace App\Modules\Customers\Service;


use App\Models\CustomersModel;
use App\Modules\Customers\Exports\CustomersExport;
use App\Modules\Customers\Imports\CustomersImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Modules\Customers\Events\CustomerActionEvent;
use Illuminate\Support\Facades\Log;

class CustomersService
{
    public function __construct()
    {
    }

    /**
     * Thống kê số lượng khách hàng.
     */
    public function stats(array $filters): array
    {
        $base = CustomersModel::filter($filters);

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
        return CustomersModel::filter($filters)
            ->paginate($limit);
    }

    /**
     * Lấy chi tiết khách hàng.
     */
    public function show(CustomersModel $customer): CustomersModel
    {
        // Nếu muốn load kèm danh sách đơn hàng thì mở comment dòng dưới
        // return $customer->load(['orders']);
        return $customer;
    }

    /**
     * Thêm mới khách hàng.
     */
    public function store(array $data): CustomersModel
    {
        $customer = DB::transaction(function () use ($data) {
            return CustomersModel::create($data);
        });

        // Tùy chọn: Bắn realtime nếu cần
        event(new CustomerActionEvent('created', $customer->toArray()));

        // Ghi log ra file laravel.log
        Log::info('Tạo khách hàng thành công! ID khách hàng: ' . $customer->id);

        return $customer;
    }

    /**
     * Cập nhật khách hàng.
     */
    public function update(CustomersModel $customer, array $validated): array
    {
        try {
            $updatedCustomer = DB::transaction(function () use ($customer, $validated) {
                $customer->update($validated);
                return $customer;
            });

            event(new CustomerActionEvent('updated', $updatedCustomer->toArray()));

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
    public function destroy(CustomersModel $customer): void
    {
        $id = $customer->id;
        $customer->delete($id);

        event(new CustomerActionEvent('deleted', ['id' => $id]));
    }

    /**
     * Xóa hàng loạt khách hàng.
     */
    public function bulkDestroy(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            CustomersModel::whereIn('id', $ids)->delete();
        });

        event(new CustomerActionEvent('bulk-deleted', ['ids' => $ids]));
    }

    /**
     * Cập nhật trạng thái hàng loạt.
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        CustomersModel::whereIn('id', $ids)->update(['status' => $status]);

        event(new CustomerActionEvent('bulk-status-updated', ['ids' => $ids, 'status' => $status]));
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
