<?php
namespace App\Modules\Orders\Router;
use Illuminate\Support\Facades\Route;
use App\Modules\Orders\Controllers\OrdersController;

// =================================================================
// 1. CÁC ROUTE TĨNH (Static Routes) - Bắt buộc phải đặt lên trên cùng
// =================================================================
Route::get('stats', [OrdersController::class, 'stats']);
Route::get('export', [OrdersController::class, 'export']);
Route::post('import', [OrdersController::class, 'import']);
Route::post('bulk-delete', [OrdersController::class, 'bulkDestroy']);
Route::post('bulk-status', [OrdersController::class, 'bulkUpdateStatus']);

// =================================================================
// 2. CÁC ROUTE CƠ BẢN (Danh sách & Thêm mới)
// =================================================================
Route::get('/', [OrdersController::class, 'index']);
Route::post('/', [OrdersController::class, 'store']);

// =================================================================
// 3. CÁC ROUTE ĐỘNG (Dynamic Routes) - Bắt buộc đặt dưới cùng
// CHÚ Ý: Đã đổi {id} thành {order} để fix lỗi trả về NULL
// =================================================================
Route::get('{order}', [OrdersController::class, 'show']);
Route::put('{order}', [OrdersController::class, 'update']);
Route::patch('{order}', [OrdersController::class, 'update']);
Route::delete('{order}', [OrdersController::class, 'destroy']);
