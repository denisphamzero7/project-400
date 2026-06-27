<?php
use Illuminate\Support\Facades\Route;

// Nhóm các routes cho module Customers với prefix là 'customers'
Route::prefix('customers')->group(function () {
    require base_path('app/Module/Customers/Router/Customers.php');
});
