<?php

use Illuminate\Support\Facades\Route;
use App\Module\Customers\Controllers\CustomersController;


    Route::get('/', [CustomersController::class, 'index']);
    Route::post('/', [CustomersController::class, 'store']);
    Route::get('{id}', [CustomersController::class, 'show']);
    Route::put('{id}', [CustomersController::class, 'update']);
    Route::patch('{id}', [CustomersController::class, 'update']); // Often included for RESTful updates
    Route::delete('{id}', [CustomersController::class, 'destroy']);

    // Route for bulk updating customer statuses
    Route::post('bulk-status', [CustomersController::class, 'bulkUpdateStatus']);
    // Alternatively, if PUT/PATCH is preferred for updates (even bulk)
    // Route::put('bulk-status', [CustomersController::class, 'bulkUpdateStatus']);
    // Route::patch('bulk-status', [CustomersController::class, 'bulkUpdateStatus']);

