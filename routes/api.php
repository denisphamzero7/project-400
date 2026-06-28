<?php
use Illuminate\Support\Facades\Route;

// Nhóm các routes cho module Customers với prefix là 'customers'
Route::prefix('customers')->group(function () {
    require base_path('app/Modules/Customers/Router/Customers.php');
});

Route::prefix('products')->group(function () {
    require base_path('app/Modules/Products/Router/Product.php');
});

Route::prefix('orders')->group(function () {
    require base_path('app/Modules/Orders/Router/Order.php');
});
