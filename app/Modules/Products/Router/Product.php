<?php
namespace App\Modules\Products\Router;
use Illuminate\Support\Facades\Route;
use App\Modules\Products\Controllers\ProductsController;

// =================================================================
// 1. CÁC ROUTE TĨNH (Static Routes) - Bắt buộc phải đặt lên trên cùng
// =================================================================
Route::get('stats', [ProductsController::class, 'stats']);
Route::get('export', [ProductsController::class, 'export']);
Route::post('import', [ProductsController::class, 'import']);
Route::post('bulk-delete', [ProductsController::class, 'bulkDestroy']);
Route::post('bulk-status', [ProductsController::class, 'bulkUpdateStatus']);

// =================================================================
// 2. CÁC ROUTE CƠ BẢN (Danh sách & Thêm mới)
// =================================================================
Route::get('/', [ProductsController::class, 'index']);
Route::post('/', [ProductsController::class, 'store']);

// =================================================================
// 3. CÁC ROUTE ĐỘNG (Dynamic Routes) - Bắt buộc đặt dưới cùng
// CHÚ Ý: Đã đổi {id} thành {customer} để fix lỗi trả về NULL
// =================================================================
Route::get('{customer}', [ProductsController::class, 'show']);
Route::put('{customer}', [ProductsController::class, 'update']);
Route::patch('{customer}', [ProductsController::class, 'update']);
Route::delete('{customer}', [ProductsController::class, 'destroy']);
