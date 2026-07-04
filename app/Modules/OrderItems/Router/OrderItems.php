<?php
namespace App\Modules\OrderItems\Router;
use Illuminate\Support\Facades\Route;
use App\Modules\OrderItems\Controllers\OrderItemsController;

// =================================================================
// 1. CÁC ROUTE TĨNH (Static Routes) - Bắt buộc phải đặt lên trên cùng
// =================================================================
Route::get('stats', [OrderItemsController::class, 'stats']);
Route::get('export', [OrderItemsController::class, 'export']);
Route::post('import', [OrderItemsController::class, 'import']);
Route::post('bulk-delete', [OrderItemsController::class, 'bulkDestroy']);
Route::post('bulk-status', [OrderItemsController::class, 'bulkUpdateStatus']);

// =================================================================
// 2. CÁC ROUTE CƠ BẢN (Danh sách & Thêm mới)
// =================================================================
Route::get('/', [OrderItemsController::class, 'index']);
Route::post('/', [OrderItemsController::class, 'store']);

// =================================================================
// 3. CÁC ROUTE ĐỘNG (Dynamic Routes) - Bắt buộc đặt dưới cùng
// CHÚ Ý: Đã đổi {id} thành {order} để fix lỗi trả về NULL
// =================================================================
Route::get('{orderItem}', [OrderItemsController::class, 'show']);
Route::put('{orderItem}', [OrderItemsController::class, 'update']);
Route::patch('{orderItem}', [OrderItemsController::class, 'update']);
Route::delete('{orderItem}', [OrderItemsController::class, 'destroy']);
