<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Customers\Controllers\CustomersController;

// =================================================================
// 1. CÁC ROUTE TĨNH (Static Routes) - Bắt buộc phải đặt lên trên cùng
// =================================================================
Route::get('stats', [CustomersController::class, 'stats']);
Route::get('export', [CustomersController::class, 'export']);
Route::post('import', [CustomersController::class, 'import']);
Route::post('bulk-delete', [CustomersController::class, 'bulkDestroy']);
Route::post('bulk-status', [CustomersController::class, 'bulkUpdateStatus']);

// =================================================================
// 2. CÁC ROUTE CƠ BẢN (Danh sách & Thêm mới)
// =================================================================
Route::get('/', [CustomersController::class, 'index']);
Route::post('/', [CustomersController::class, 'store']);

// =================================================================
// 3. CÁC ROUTE ĐỘNG (Dynamic Routes) - Bắt buộc đặt dưới cùng
// CHÚ Ý: Đã đổi {id} thành {customer} để fix lỗi trả về NULL
// =================================================================
Route::get('{customer}', [CustomersController::class, 'show']);
Route::put('{customer}', [CustomersController::class, 'update']);
Route::patch('{customer}', [CustomersController::class, 'update']);
Route::delete('{customer}', [CustomersController::class, 'destroy']);
