<?php

namespace App\Jobs;

use App\Enums\OrdersStatusEnum;
use App\Models\OrderModel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExpirePendingOrdersJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Job Queue: Đang chạy tiến trình quét đơn hàng hết hạn ngầm.');

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
            Log::info("Job Queue: Đã xử lý hết hạn thành công {$expiredCount} đơn hàng chờ xử lý cũ hơn {$hours} giờ.");
        } else {
            Log::info('Job Queue: Không có đơn hàng đang chờ xử lý nào đến hạn.');
        }
    }
}
