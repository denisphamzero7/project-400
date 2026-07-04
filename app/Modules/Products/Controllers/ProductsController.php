<?php

namespace App\Modules\Products\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProductModel;
use App\Modules\Products\Requests\FilterRequest;
use App\Modules\Products\Service\ProductsService;

use App\Modules\Products\Requests\StoreProductsRequest;
use App\Modules\Products\Requests\UpdateProductsRequest;
use App\Modules\Products\Requests\BulkUpdateStatusProductsRequest;
use App\Modules\Products\Requests\BulkDestroyProductsRequest; // Thêm Request cho xóa hàng loạt
use App\Modules\Products\Requests\ImportProductsRequest;      // Thêm Request cho import
use App\Modules\Products\Resources\ProductsCollection;
use App\Traits\RespondsWithJson;

// Kéo Resource và Collection vào

use App\Modules\Products\Resources\ProductsResource;

class ProductsController extends Controller
{
    use RespondsWithJson;

    public function __construct(private ProductsService $ProductsService) {}

    /**
     * Danh sách sản phẩm (Có phân trang và bộ lọc)
     */
    public function index(FilterRequest $request)
    {
        $products = $this->ProductsService->index($request->all(), (int) ($request->limit ?? 10));

        return $this->successCollection(new ProductsCollection($products));
    }

    /**
     * Thống kê sản phẩm
     */
    public function stats(FilterRequest $request)
    {
        return $this->success($this->ProductsService->stats($request->all()));
    }

    /**
     * Chi tiết 1 sản phẩm
     */
    public function show(ProductModel $product)
    {
        $product = $this->ProductsService->show($product);

        return $this->successResource(new ProductsResource($product));
    }

    /**
     * Tạo sản phẩm mới
     */
    public function store(StoreProductsRequest $request)
    {
        $data = $request->validated();
        try {
            $product = $this->ProductsService->store($data);

            return $this->successResource(new ProductsResource($product), 'Sản phẩm đã được tạo thành công!', 201);
        } catch (\Throwable $th) {
            return $this->error('Tạo sản phẩm thất bại!', 500, null, $th->getMessage());
        }
    }

    /**
     * Cập nhật thông tin sản phẩm
     */
    public function update(UpdateProductsRequest $request, ProductModel $product)
    {
        $result = $this->ProductsService->update($product, $request->validated());

        if (!$result['ok']) {
            return $this->error($result['message'], $result['code'], null, $result['error_code']);
        }

        return $this->successResource(new ProductsResource($result['product']), 'Cập nhật thông tin thành công!');
    }

    /**
     * Xóa 1 sản phẩm
     */
    public function destroy(ProductModel $product)
    {
        $this->ProductsService->destroy($product);

        return $this->success(null, 'Sản phẩm đã được xóa!');
    }

    /**
     * Xóa hàng loạt sản phẩm
     */
    public function bulkDestroy(BulkDestroyProductsRequest $request)
    {
        $this->ProductsService->bulkDestroy($request->ids);

        return $this->success(null, 'Đã xóa thành công các sản phẩm được chọn!');
    }

    /**
     * Cập nhật trạng thái hàng loạt cho sản phẩm
     */
    public function bulkUpdateStatus(BulkUpdateStatusProductsRequest $request)
    {
        $this->ProductsService->bulkUpdateStatus($request->ids, $request->status);

        return $this->success(null, 'Cập nhật trạng thái hàng loạt cho sản phẩm thành công!');
    }

    /**
     * Xuất danh sách sản phẩm (Excel)
     */
    public function export(FilterRequesýt $request)
    {
        // Trả thẳng file BinaryFileResponse từ Service về Client
        return $this->ProductsService->export($request->all());
    }

    /**
     * Nhập danh sách sản phẩm từ file Excel
     */
    public function import(ImportProductsRequest $request)
    {
        $this->ProductsService->import($request->file('file'));

        return $this->success(null, 'Import dữ liệu sản phẩm thành công.');
    }
}
