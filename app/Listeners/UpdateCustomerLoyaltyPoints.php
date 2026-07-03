<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\CustomersModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateCustomerLoyaltyPoints implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaid $event): void
    {
        $customer = $event->order->customer;

        if ($customer) {
            // Logic tính điểm: ví dụ: 10,000 VNĐ = 1 điểm
            $pointsToAdd = floor($event->order->total_amount / 10000);

            if ($pointsToAdd > 0) {
                $customer->increment('loyalty_points', $pointsToAdd);
                $customer->refresh();
                Log::info("Đã cộng {$pointsToAdd} điểm thưởng cho khách hàng: {$customer->name}. Tổng điểm mới: {$customer->loyalty_points}");
            }
        }
    }
}
