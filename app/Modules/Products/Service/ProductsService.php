<?php

namespace App\Modules\Products\Service;


use App\Models\ProductModel;


use App\Modules\Products\Exports\ProductsExport;
use App\Modules\Products\Imports\ProductsImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// Giả định bạn sẽ tạo một Event tương tự như JobActionEvent cho Customer
// use App\Moduless\Customers\Events\CustomerActionEvent;

class ProductsService
{
    public function __construct()
    {
    }

    /**
     * Thống kê số lượng sản phẩm.
     */
    public function stats(array $filters): array
    {
        $base = ProductModel::filter($filters);

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
        return ProductModel::filter($filters)
            ->paginate($limit);
    }

    /**
     * Lấy chi tiết sản phẩm.
     */
    public function show(ProductModel $product): ProductModel
    {
        // Load kèm danh sách các order items liên quan đến sản phẩm này
        return $product->load(['orderItems']);
    }

    /**
     * Thêm mới sản phẩm.
     */
    public function store(array $data): ProductModel
    {
        $product = DB::transaction(function () use ($data) {
            return ProductModel::create($data);
        });

        // Tùy chọn: Bắn realtime nếu cần
        // broadcast(new ProductActionEvent('product-created', $product->toArray()));

        return $product;
    }

    /**
     * Cập nhật sản phẩm.
     */
    public function update(ProductModel $product, array $validated): array
    {
        try {
            $updatedProduct = DB::transaction(function () use ($product, $validated) {
                $product->update($validated);
                return $product;
            });

            // broadcast(new ProductActionEvent('product-updated', $updatedProduct->toArray()));

            return [
                'ok' => true,
                'product' => $updatedProduct
            ];
        } catch (\Exception $e) {
            return [
                'ok' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage(),
                'code' => 500,
                'error_code' => 'PRODUCT_UPDATE_ERROR'
            ];
        }
    }

    /**
     * Xóa sản phẩm.
     */
    public function destroy(ProductModel $product): void
    {
        $product->delete();

        // broadcast(new ProductActionEvent('product-deleted', $product->toArray()));
    }

    /**
     * Xóa hàng loạt khách hàng.
     */
    public function bulkDestroy(array $ids): void
    {
        DB::transaction(function () use ($ids) {
            ProductModel::whereIn('id', $ids)->delete();
        });

        // broadcast(new ProductActionEvent('product-bulk-deleted', ['ids' => $ids]));
    }

    /**
     * Cập nhật trạng thái hàng loạt.
     */
    public function bulkUpdateStatus(array $ids, string $status): void
    {
        ProductModel::whereIn('id', $ids)->update(['status' => $status]);

        // broadcast(new ProductActionEvent('product-bulk-status-updated', ['ids' => $ids, 'status' => $status]));
    }

    /**
     * Xuất danh sách sản phẩm (Excel).
     */
    public function export(array $filters): BinaryFileResponse
    {
        return Excel::download(
            new ProductsExport($filters), // Truyền file Export đã sửa
            'products.xlsx'
        );
    }

    /**
     * Nhập danh sách sản phẩm từ file.
     */
    public function import($file): void
    {
        Excel::import(new ProductsImport(), $file); // Truyền file Import đã sửa
    }
}
