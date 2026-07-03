<?php
namespace App\Modules\Reports\Router;

use Illuminate\Support\Facades\Route;
use App\Modules\Reports\Controllers\ReportsController;

Route::get('revenue', [ReportsController::class, 'revenue']);
