<?php

namespace App\Console\Commands;

use App\Enums\OrdersStatusEnum;
use App\Models\OrderModel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ExpirePendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tìm các đơn hàng đang chờ xử lý có thời gian xử lý cũ hơn khoảng thời gian đã cấu hình và đặt trạng thái của chúng thành hết hạn.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Bắt đầu hết hạn các đơn hàng đang chờ xử lý cũ...');
        Log::info('Lập lịch: Đang chạy lệnh ExpirePendingOrders.');

        $hours = config('orders.expire_hours', 48);
        $thresholdTime = now()->subHours($hours);

        $orders = OrderModel::where('status', OrdersStatusEnum::PENDING)
            ->where('created_at', '<=', $thresholdTime)
            ->get();

        $expiredCount = 0;
        foreach ($orders as $order) {
            $order->update(['status' => OrdersStatusEnum::EXPIRED]);
            $expiredCount++;
        }

        if ($expiredCount > 0) {
            $this->info("Successfully expired {$expiredCount} orders.");
            Log::info("Lập lịch: Đã hết hạn {$expiredCount} đơn hàng đang chờ xử lý cũ hơn {$hours} giờ.");
        } else {
            $this->info('Không có đơn hàng nào sắp hết hạn.');
            Log::info('Lập lịch: Không có đơn hàng đang chờ xử lý nào để hết hạn.');
        }

        $this->info('Finished expiring old pending orders.');
        return 0;
    }
}
