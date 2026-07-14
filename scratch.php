<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\OrderModel;
use App\Enums\OrdersStatusEnum;

$filters = []; // Same as empty request
$stats = OrderModel::filter($filters)
    ->reorder()
    ->selectRaw('
        COUNT(id) as total,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as cancelled,
        SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as expired
    ', [
        OrdersStatusEnum::PENDING->value,
        OrdersStatusEnum::COMPLETED->value,
        OrdersStatusEnum::CANCELLED->value,
        OrdersStatusEnum::EXPIRED->value
    ])
    ->first();

print_r($stats->toArray());
